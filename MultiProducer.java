import org.apache.kafka.clients.producer.KafkaProducer;
import org.apache.kafka.clients.producer.ProducerConfig;
import org.apache.kafka.clients.producer.ProducerRecord;
import org.apache.kafka.common.serialization.StringSerializer;

import java.util.Properties;
import java.util.Scanner;

public class MultiProducer {
    private static final String TOPIC_NAME = "test-topic";
    private static final String BOOTSTRAP_SERVERS = "localhost:9092";
    
    public static void main(String[] args) {
        System.out.println("=== Multi-Producer Kafka Demo ===");
        
        // Cấu hình Producer
        Properties props = new Properties();
        props.put(ProducerConfig.BOOTSTRAP_SERVERS_CONFIG, BOOTSTRAP_SERVERS);
        props.put(ProducerConfig.KEY_SERIALIZER_CLASS_CONFIG, StringSerializer.class.getName());
        props.put(ProducerConfig.VALUE_SERIALIZER_CLASS_CONFIG, StringSerializer.class.getName());
        
        // Tạo producer
        KafkaProducer<String, String> producer = new KafkaProducer<>(props);
        
        // Đọc producer ID từ argument hoặc dùng default
        String producerId = args.length > 0 ? args[0] : "P1";
        
        Scanner scanner = new Scanner(System.in);
        try {
            System.out.println("Producer " + producerId + " started. Enter messages (type 'exit' to quit):");
            
            while (true) {
                System.out.print("Enter message: ");
                String message = scanner.nextLine();
                
                if ("exit".equalsIgnoreCase(message.trim())) {
                    break;
                }
                
                // Tạo key với producerId
                String key = producerId + "-" + System.currentTimeMillis();
                
                // Tạo record với prefix producer ID
                String value = "[" + producerId + "] " + message;
                ProducerRecord<String, String> record = new ProducerRecord<>(TOPIC_NAME, key, value);
                
                // Gửi message
                producer.send(record, (metadata, exception) -> {
                    if (exception != null) {
                        System.err.println("Error sending message: " + exception.getMessage());
                    } else {
                        System.out.println("✓ Message sent successfully from " + producerId);
                        System.out.println("  Topic: " + metadata.topic());
                        System.out.println("  Partition: " + metadata.partition());
                        System.out.println("  Offset: " + metadata.offset());
                        System.out.println("  Timestamp: " + metadata.timestamp());
                        System.out.println("---");
                    }
                });
                
                // Flush để đảm bảo message được gửi
                producer.flush();
            }
            
        } finally {
            scanner.close();
            producer.close();
            System.out.println("Producer " + producerId + " closed.");
        }
    }
}