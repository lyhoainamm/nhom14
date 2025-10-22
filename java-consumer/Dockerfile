FROM maven:3.9-eclipse-temurin-17 AS build
WORKDIR /app
COPY pom.xml .
RUN mvn -q -DskipTests dependency:go-offline
COPY src ./src
RUN mvn -q -DskipTests package

FROM eclipse-temurin:17-jre
WORKDIR /app
COPY --from=build /app/target/java-consumer-1.0.0.jar /app/app.jar
ENV BOOTSTRAP=kafka:9092 GROUP_ID=nhom14-webapp-consumer TOPICS=order-viewed \
    JDBC_URL="jdbc:mysql://mysql:3306/nhom14_ui?useUnicode=true&characterEncoding=utf8&useSSL=false&allowPublicKeyRetrieval=true" \
    JDBC_USER=root JDBC_PASS=root
ENTRYPOINT ["java","-jar","/app/app.jar"]
