import javax.swing.*;
import javax.swing.border.EmptyBorder;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.nio.charset.StandardCharsets;
import java.util.concurrent.Executors;

/**
 * Minimal Swing UI to produce and consume Kafka messages using Dockerized Kafka.
 * It shells out to `docker exec` to avoid requiring Kafka client jars locally.
 */
public class KafkaGui extends JFrame {
    private final JTextField bootstrapServersField;
    private final JTextField topicField;
    private final JTextArea producerInputArea;
    private final JButton sendButton;
    private final JTextArea consumerOutputArea;
    private final JButton startConsumerButton;
    private final JButton stopConsumerButton;

    private Process consumerProcess;

    public KafkaGui() {
        super("Kafka Demo UI (Docker)");
        setDefaultCloseOperation(WindowConstants.EXIT_ON_CLOSE);

        JPanel root = new JPanel(new BorderLayout(10, 10));
        root.setBorder(new EmptyBorder(12, 12, 12, 12));
        setContentPane(root);

        JPanel configPanel = new JPanel(new GridBagLayout());
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(4, 4, 4, 4);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        JLabel bsLabel = new JLabel("Bootstrap Servers:");
        bootstrapServersField = new JTextField("localhost:9092", 20);
        JLabel topicLabel = new JLabel("Topic:");
        topicField = new JTextField("test-topic", 20);

        gbc.gridx = 0; gbc.gridy = 0; configPanel.add(bsLabel, gbc);
        gbc.gridx = 1; gbc.gridy = 0; configPanel.add(bootstrapServersField, gbc);
        gbc.gridx = 0; gbc.gridy = 1; configPanel.add(topicLabel, gbc);
        gbc.gridx = 1; gbc.gridy = 1; configPanel.add(topicField, gbc);

        root.add(configPanel, BorderLayout.NORTH);

        JSplitPane splitPane = new JSplitPane(JSplitPane.HORIZONTAL_SPLIT);
        splitPane.setResizeWeight(0.5);

        // Producer panel
        JPanel producerPanel = new JPanel(new BorderLayout(6, 6));
        producerPanel.setBorder(BorderFactory.createTitledBorder("Producer"));
        producerInputArea = new JTextArea(10, 40);
        producerInputArea.setLineWrap(true);
        producerInputArea.setWrapStyleWord(true);
        sendButton = new JButton("Send");
        sendButton.addActionListener(this::onSendClicked);
        producerPanel.add(new JScrollPane(producerInputArea), BorderLayout.CENTER);
        producerPanel.add(sendButton, BorderLayout.SOUTH);

        // Consumer panel
        JPanel consumerPanel = new JPanel(new BorderLayout(6, 6));
        consumerPanel.setBorder(BorderFactory.createTitledBorder("Consumer"));
        consumerOutputArea = new JTextArea(18, 60);
        consumerOutputArea.setEditable(false);
        consumerOutputArea.setLineWrap(true);
        consumerOutputArea.setWrapStyleWord(true);
        JPanel consumerButtons = new JPanel(new FlowLayout(FlowLayout.LEFT));
        startConsumerButton = new JButton("Start Consumer");
        startConsumerButton.addActionListener(this::onStartConsumer);
        stopConsumerButton = new JButton("Stop Consumer");
        stopConsumerButton.addActionListener(this::onStopConsumer);
        consumerButtons.add(startConsumerButton);
        consumerButtons.add(stopConsumerButton);
        consumerPanel.add(consumerButtons, BorderLayout.NORTH);
        consumerPanel.add(new JScrollPane(consumerOutputArea), BorderLayout.CENTER);

        splitPane.setLeftComponent(producerPanel);
        splitPane.setRightComponent(consumerPanel);
        root.add(splitPane, BorderLayout.CENTER);

        setMinimumSize(new Dimension(1000, 600));
        setLocationRelativeTo(null);
    }

    private void onSendClicked(ActionEvent e) {
        String message = producerInputArea.getText().trim();
        if (message.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Nhập message trước khi gửi", "Thông báo", JOptionPane.INFORMATION_MESSAGE);
            return;
        }
        String bootstrap = bootstrapServersField.getText().trim();
        String topic = topicField.getText().trim();
        sendButton.setEnabled(false);
        Executors.newSingleThreadExecutor().submit(() -> {
            try {
                // Produce via docker exec using stdin pipe
                ProcessBuilder pb = new ProcessBuilder(
                        "cmd.exe", "/c",
                        "echo " + escapeForCmd(message) + " | docker exec -i broker /opt/kafka/bin/kafka-console-producer.sh --topic " + topic + " --bootstrap-server " + bootstrap
                );
                pb.redirectErrorStream(true);
                Process proc = pb.start();
                drainToNull(proc.getInputStream());
                int code = proc.waitFor();
                SwingUtilities.invokeLater(() -> {
                    if (code == 0) {
                        JOptionPane.showMessageDialog(this, "Đã gửi message", "OK", JOptionPane.INFORMATION_MESSAGE);
                    } else {
                        JOptionPane.showMessageDialog(this, "Gửi thất bại (exit=" + code + ")", "Lỗi", JOptionPane.ERROR_MESSAGE);
                    }
                });
            } catch (Exception ex) {
                SwingUtilities.invokeLater(() -> JOptionPane.showMessageDialog(this, ex.getMessage(), "Lỗi", JOptionPane.ERROR_MESSAGE));
            } finally {
                SwingUtilities.invokeLater(() -> sendButton.setEnabled(true));
            }
        });
    }

    private void onStartConsumer(ActionEvent e) {
        if (consumerProcess != null && consumerProcess.isAlive()) {
            return;
        }
        String bootstrap = bootstrapServersField.getText().trim();
        String topic = topicField.getText().trim();
        try {
            ProcessBuilder pb = new ProcessBuilder(
                    "cmd.exe", "/c",
                    "docker exec broker /opt/kafka/bin/kafka-console-consumer.sh --topic " + topic + " --bootstrap-server " + bootstrap + " --from-beginning"
            );
            pb.redirectErrorStream(true);
            consumerProcess = pb.start();
            InputStream is = consumerProcess.getInputStream();
            Executors.newSingleThreadExecutor().submit(() -> streamToTextArea(is));
        } catch (IOException ex) {
            JOptionPane.showMessageDialog(this, ex.getMessage(), "Lỗi", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void onStopConsumer(ActionEvent e) {
        if (consumerProcess != null) {
            consumerProcess.destroy();
            consumerProcess = null;
        }
    }

    private void streamToTextArea(InputStream inputStream) {
        try (BufferedReader br = new BufferedReader(new InputStreamReader(inputStream, StandardCharsets.UTF_8))) {
            String line;
            while ((line = br.readLine()) != null) {
                final String append = line + "\n";
                SwingUtilities.invokeLater(() -> consumerOutputArea.append(append));
            }
        } catch (IOException ignored) {
        }
    }

    private void drainToNull(InputStream inputStream) {
        try (BufferedReader br = new BufferedReader(new InputStreamReader(inputStream, StandardCharsets.UTF_8))) {
            while (br.readLine() != null) {
                // discard
            }
        } catch (IOException ignored) {
        }
    }

    private String escapeForCmd(String text) {
        return text.replace("^", "^^").replace("&", "^&").replace("|", "^|").replace(">", "^").replace("<", "^");
    }

    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> new KafkaGui().setVisible(true));
    }
}


