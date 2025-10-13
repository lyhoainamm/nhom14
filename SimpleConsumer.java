import org.apache.kafka.clients.consumer.ConsumerConfig;
import org.apache.kafka.clients.consumer.ConsumerRecord;
import org.apache.kafka.clients.consumer.ConsumerRecords;
import org.apache.kafka.clients.consumer.KafkaConsumer;
import org.apache.kafka.common.serialization.StringDeserializer;

import java.time.Duration;
import java.util.Arrays;
import java.util.Properties;

/**
 * Simple Kafka Consumer - Nhận tin nhắn từ Kafka topic
 * 
 * Chức năng: Nhận và hiển thị các tin nhắn từ topic "test-topic"
 * Sử dụng: Kafka Consumer API với String deserializer
 */
public class SimpleConsumer {
    
    private static final String TOPIC_NAME = "test-topic";
    private static final String BOOTSTRAP_SERVERS = "localhost:9092";
    private static final String GROUP_ID = "test-consumer-group";
    
    public static void main(String[] args) {
        System.out.println("=== Kafka Consumer Demo ===");
        
        // Cấu hình Consumer
        Properties props = new Properties();
        props.put(ConsumerConfig.BOOTSTRAP_SERVERS_CONFIG, BOOTSTRAP_SERVERS);
        props.put(ConsumerConfig.GROUP_ID_CONFIG, GROUP_ID);
        props.put(ConsumerConfig.KEY_DESERIALIZER_CLASS_CONFIG, StringDeserializer.class.getName());
        props.put(ConsumerConfig.VALUE_DESERIALIZER_CLASS_CONFIG, StringDeserializer.class.getName());
        props.put(ConsumerConfig.AUTO_OFFSET_RESET_CONFIG, "earliest"); // Đọc từ đầu topic
        
        // Tạo Consumer
        KafkaConsumer<String, String> consumer = new KafkaConsumer<>(props);
        
        try {
            // Subscribe topic
            consumer.subscribe(Arrays.asList(TOPIC_NAME));
            System.out.println("Đã subscribe topic: " + TOPIC_NAME);
            System.out.println("Đang chờ tin nhắn... (Nhấn Ctrl+C để dừng)");
            System.out.println("==========================================");
            
            // Vòng lặp nhận tin nhắn
            while (true) {
                ConsumerRecords<String, String> records = consumer.poll(Duration.ofMillis(1000));
                
                for (ConsumerRecord<String, String> record : records) {
                    System.out.println("📨 Nhận được tin nhắn:");
                    System.out.println("   Key: " + record.key());
                    System.out.println("   Value: " + record.value());
                    System.out.println("   Topic: " + record.topic());
                    System.out.println("   Partition: " + record.partition());
                    System.out.println("   Offset: " + record.offset());
                    System.out.println("   Timestamp: " + record.timestamp());
                    System.out.println("   Headers: " + record.headers());
                    System.out.println("---");
                }
                
                // Nếu không có tin nhắn nào, hiển thị thông báo
                if (records.isEmpty()) {
                    System.out.println("⏳ Chưa có tin nhắn mới...");
                }
            }
            
        } catch (Exception e) {
            System.err.println("Lỗi trong Consumer: " + e.getMessage());
            e.printStackTrace();
        } finally {
            // Đóng Consumer
            consumer.close();
            System.out.println("Consumer đã đóng.");
        }
    }
}
