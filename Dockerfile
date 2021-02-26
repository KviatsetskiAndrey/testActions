FROM alpine:3.11

RUN apk add ca-certificates tzdata

WORKDIR /app

COPY sender /app/sender

ENTRYPOINT ["/app/sender"]
