package main

import (
	"fmt"
	"github.com/nats-io/stan.go"
	"strconv"
	"time"
)

func main() {
	connectionLostHandler := stan.SetConnectionLostHandler(func(cn stan.Conn, err error) {
		fmt.Println("Connection lost", "err", err)
	})

	conn, err := stan.Connect(
		"nats-streaming",
		"sender",
		stan.NatsURL("nats://nats:4222"),
		connectionLostHandler,
	)
	fmt.Println(err)

	fmt.Println("Sender start")
	numLoops := 10000
	timePerIteration := time.Duration(100000) * time.Second / time.Duration(numLoops)
	ticker := time.NewTicker(timePerIteration)
	for i := 0; i < numLoops; i++ {
		err := conn.Publish("test", []byte("test"+strconv.Itoa(i)))
		if err != nil {
			fmt.Println(err)
		}
		fmt.Println("test", "test", i)
		<-ticker.C
	}
	ticker.Stop()
}
