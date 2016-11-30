package main

import (
    "fmt"
    "log"
    "encoding/json"
    "net/url"
    "bytes"
    "strings"
    "github.com/garyburd/redigo/redis"
    "time"
    "net/http"
    "io/ioutil"
    "regexp"
)

const DEFAULT_REPLACEMENT_VALUE string = "null"
const REDIS_PASSWORD string = "redisauthISpasswhee"
const REDIS_PORT string = ":50002"
const REDIS_EXPIRE_DURATION int = 30 // seconds before which redis keys expire. Decrease to free memory faster, increase to allow larger latency. This duration is measured from *after* the postback http request has responded, so it does not include that latency.

const LOG_TO_DATABASE bool = false // For development purposes - adds extra traffic to redis and MySQL

func makeHttpRequest(requestUrl string, requestKey string, responseConnection redis.Conn, postbackLog *log.Logger ) {
    fmt.Printf("%s Posting to %s\n", requestKey, requestUrl)
    deliveryTime := time.Now()
    response, err := http.Get(requestUrl)
    if err != nil {
        fmt.Printf("Error in url request: %s\n", err)
    }

    if response != nil {
        fmt.Println("Non-nil response within makeHttpRequest")
        defer response.Body.Close()
        body, err := ioutil.ReadAll(response.Body)
        if err != nil {
            fmt.Printf("Error reading response body: %s\n", err)
        }

        responseObject := map[string]string {
            "method":"GET",
            "url":requestUrl,
        }
        responseString, _ := json.Marshal(responseObject)
        result, err := responseConnection.Do("set", requestKey, responseString)
        if err != nil {
            fmt.Printf("error in setting postback response: %s, result is: %s\n", err, result)
        }

        postbackLog.Printf("%s Delivery time: %s\n", requestKey, deliveryTime.String())
        postbackLog.Printf("%s Response Body: %s\n", requestKey, string(body))
        postbackLog.Printf("%s Response Code: %s\n", requestKey, string(response.StatusCode))
        postbackLog.Printf("%s Response Time: %s\n", requestKey, time.Now().String())

        result, err = responseConnection.Do("expire", requestKey, REDIS_EXPIRE_DURATION)

        if LOG_TO_DATABASE {
            dbLog := map[string]string {
                "deliverytime":deliveryTime.String(),
                "responsebody":string(body),
                "responsecode":string(response.StatusCode),
                "responsetime":time.Now().String(),
            }
            logJsonString, _ := json.Marshal(dbLog)

            var buffer bytes.Buffer
            buffer.WriteString(requestKey)
            buffer.WriteString("_log")
            logKey := buffer.String()
            result, err = responseConnection.Do("set", logKey, logJsonString)

            result, err = responseConnection.Do("expire", logKey, REDIS_EXPIRE_DURATION)
        }
    } else {
        fmt.Println("%s nil response to http request.\n", requestKey)
    }
}

func createRedisConnection() (newConnection redis.Conn) {
    newConnection, err := redis.Dial("tcp", REDIS_PORT)
    if err != nil {
        fmt.Println("Error creating connection:")
        fmt.Println(err)
    }
    newConnection.Do("AUTH", REDIS_PASSWORD)
    return newConnection
}

func main() {
    subscribeConnection := createRedisConnection()
    defer subscribeConnection.Close()

    subscription := redis.PubSubConn{Conn: subscribeConnection}
    subscription.Subscribe("tasks")

    var logBuffer bytes.Buffer
    postbackLog := log.New(&logBuffer, "postback: ", log.Lshortfile)

    responseConnection := createRedisConnection()
    for {
        switch v := subscription.Receive().(type) {
        case redis.PMessage:
            fmt.Printf("%s: pmessage: %s\n", v.Channel, v.Data)
        case redis.Message:
            var req map[string]interface{}
            if err := json.Unmarshal(v.Data, &req); err != nil {
                log.Printf("ERR: Error in unmarshalling process. raw message: %s", v.Data)
                panic(err)
            }

            postbackUrl := req["endpoint"].(map[string]interface{})["url"].(string)
            postbackVars := req["data"].([]interface{})

            for _,element := range postbackVars {
                element = element.(map[string]interface{})

                for key,val := range element.(map[string]interface{}) {
                    var buffer bytes.Buffer
                    buffer.WriteString("{")
                    buffer.WriteString(key)
                    buffer.WriteString("}")
                    postbackUrl = strings.Replace(postbackUrl, buffer.String(), url.QueryEscape(val.(string)), -1)
                }
            }
            fmt.Printf("Request URL after replacing non-default values is %s", postbackUrl)

            replaceExp := regexp.MustCompile("{.*}")
            postbackUrl = replaceExp.ReplaceAllLiteralString(postbackUrl, DEFAULT_REPLACEMENT_VALUE)

            // Ok, it's kind of sexy how go handles concurrency
            go makeHttpRequest(postbackUrl, req["requestKey"].(string), responseConnection, postbackLog)
        case redis.Subscription:
            fmt.Printf("%s: %s %d\n", v.Channel, v.Kind, v.Count)
        case error:
            fmt.Println("%s", v)
        }
    }
}
