package com.nhom14.consumer;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.time.Duration;
import java.util.Arrays;
import java.util.List;
import java.util.Properties;

import org.apache.kafka.clients.consumer.ConsumerConfig;
import org.apache.kafka.clients.consumer.ConsumerRecord;
import org.apache.kafka.clients.consumer.ConsumerRecords;
import org.apache.kafka.clients.consumer.KafkaConsumer;
import org.apache.kafka.common.serialization.StringDeserializer;

public class KafkaToMySQLConsumer {
    static String env(String k, String d){ String v=System.getenv(k); return (v==null||v.isBlank())?d:v; }

    public static void main(String[] args) throws Exception {
        String BOOTSTRAP = env("BOOTSTRAP","kafka:9092");
        String GROUP_ID  = env("GROUP_ID","nhom14-webapp-consumer");
        List<String> TOPICS = Arrays.asList(env("TOPICS","order-viewed").split(","));
        String JDBC_URL  = env("JDBC_URL","jdbc:mysql://mysql:3306/nhom14_ui?useUnicode=true&characterEncoding=utf8&useSSL=false&allowPublicKeyRetrieval=true");
        String JDBC_USER = env("JDBC_USER","root");
        String JDBC_PASS = env("JDBC_PASS","root");

        try(Connection conn= DriverManager.getConnection(JDBC_URL,JDBC_USER,JDBC_PASS)){
            conn.setAutoCommit(false);

            Properties p=new Properties();
            p.put(ConsumerConfig.BOOTSTRAP_SERVERS_CONFIG, BOOTSTRAP);
            p.put(ConsumerConfig.KEY_DESERIALIZER_CLASS_CONFIG, StringDeserializer.class.getName());
            p.put(ConsumerConfig.VALUE_DESERIALIZER_CLASS_CONFIG, StringDeserializer.class.getName());
            p.put(ConsumerConfig.GROUP_ID_CONFIG, GROUP_ID);
            p.put(ConsumerConfig.AUTO_OFFSET_RESET_CONFIG, "earliest");
            p.put(ConsumerConfig.ENABLE_AUTO_COMMIT_CONFIG, "false");

            try(KafkaConsumer<String,String> consumer=new KafkaConsumer<>(p)){
                consumer.subscribe(TOPICS);
                System.out.println("Consumer started: "+TOPICS);

                String sql = """
                    INSERT INTO consumed_messages(topic, mkey, mvalue, partition_id, offset_val, consumed_at, created_at, updated_at)
                    VALUES (?,?,?,?,?, NOW(), NOW(), NOW())
                """;

                while(true){
                    ConsumerRecords<String,String> recs = consumer.poll(Duration.ofMillis(500));
                    if(recs.isEmpty()) continue;

                    try(PreparedStatement ps=conn.prepareStatement(sql)){
                        for(ConsumerRecord<String,String> r: recs){
                            ps.setString(1, r.topic());
                            ps.setString(2, r.key());
                            ps.setString(3, r.value());
                            ps.setString(4, String.valueOf(r.partition()));
                            ps.setString(5, String.valueOf(r.offset()));
                            ps.addBatch();
                            System.out.printf("Consumed %s [%d@%d] key=%s value=%s%n",
                                    r.topic(), r.partition(), r.offset(), r.key(), r.value());
                        }
                        ps.executeBatch();
                        conn.commit();
                        consumer.commitSync();
                    }catch(Exception e){
                        conn.rollback();
                        System.err.println("DB error: "+e.getMessage());
                    }
                }
            }
        }
    }
}
