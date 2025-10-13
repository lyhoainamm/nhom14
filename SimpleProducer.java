import org.apache.kafka.clients.producer.KafkaProducer;
import org.apache.kafka.clients.producer.ProducerConfig;
import org.apache.kafka.clients.producer.ProducerRecord;
import org.apache.kafka.clients.producer.RecordMetadata;
import org.apache.kafka.common.serialization.StringSerializer;

import java.util.Properties;
import java.util.concurrent.Future;

/**
 * Simple Kafka Producer - Gửi tin nhắn đến Kafka topic
 * 
 * Chức năng: Gửi các tin nhắn text đến topic "test-topic"
 * Sử dụng: Kafka Producer API với String serializer
 */
public class SimpleProducer {
    
    private static final String TOPIC_NAME = "test-topic";
    private static final String BOOTSTRAP_SERVERS = "localhost:9092";
    
    public static void main(String[] args) {
        System.out.println("=== Kafka Producer Demo ===");
        
        // Cấu hình Producer
        Properties props = new Properties();
        props.put(ProducerConfig.BOOTSTRAP_SERVERS_CONFIG, BOOTSTRAP_SERVERS);
        props.put(ProducerConfig.KEY_SERIALIZER_CLASS_CONFIG, StringSerializer.class.getName());
        props.put(ProducerConfig.VALUE_SERIALIZER_CLASS_CONFIG, StringSerializer.class.getName());
        
        // Tạo Producer
        KafkaProducer<String, String> producer = new KafkaProducer<>(props);
        
        try {
            // Gửi một số tin nhắn mẫu
            String[] messages = {
                "Xin chào từ Producer!",
                "Tin nhắn số 2",
                "Kafka đang hoạt động tốt",
                "Hệ thống phân tán với Kafka",
                "Tin nhắn cuối cùng"
            };
            
            for (int i = 0; i < messages.length; i++) {
                String key = "key-" + (i + 1);
                String value = messages[i];
                
                // Tạo ProducerRecord
                ProducerRecord<String, String> record = new ProducerRecord<>(TOPIC_NAME, key, value);
                
                // Gửi tin nhắn và nhận metadata
                Future<RecordMetadata> future = producer.send(record, (metadata, exception) -> {
                    if (exception != null) {
                        System.err.println("Lỗi gửi tin nhắn: " + exception.getMessage());
                    } else {
                        System.out.println("✓ Đã gửi tin nhắn: " + value);
                        System.out.println("  Topic: " + metadata.topic());
                        System.out.println("  Partition: " + metadata.partition());
                        System.out.println("  Offset: " + metadata.offset());
                        System.out.println("  Timestamp: " + metadata.timestamp());
                        System.out.println("---");
                    }
                });
                
                // Đợi một chút giữa các tin nhắn
                Thread.sleep(1000);
            }
            
        } catch (Exception e) {
            System.err.println("Lỗi trong Producer: " + e.getMessage());
            e.printStackTrace();
        } finally {
            // Đóng Producer
            producer.close();
            System.out.println("Producer đã đóng.");
        }
    }
}
