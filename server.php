<?php

declare(strict_types=1);

error_reporting(E_ERROR | E_PARSE);

$server = stream_socket_server('tcp://0.0.0.0:8080', $errno, $errstr);

if ($server === false) {
    fwrite(STDERR, "Error: $errno: $errstr");

    exit(1);
}

sendMessage(STDERR, sprintf("Listening on: %s\n", stream_socket_get_name($server, false)));

while (true) {
    $connection = stream_socket_accept($server, -1, $clientAddress);

    if ($connection) {
      sendMessage(STDERR, "Client [{$clientAddress}] connected \n");
      sendMessage($connection, "Hello from server \n");
      sendMessage($connection, "Type !quit to exit \n");
      sendMessage($connection, "Type !time to get current time \n");
      sendMessage($connection, "[$clientAddress] > ");

      while ($buffer = fread($connection, 2048)) {
        if ($buffer !== '') {
          sendMessage($connection, "[SERVER]: $buffer");
          sendMessage($connection, "[$clientAddress] > ");
        }

        if (strpos($buffer, '!quit') !== false) {
            sendMessage(STDERR, "Client [{$clientAddress}] disconnected \n");
            sendMessage($connection, "Bye! \n");
            quit($connection);
            break;
        }

        if (strpos($buffer, '!time') !== false) {
            sendMessage($connection, date('Y-m-d H:i:s') . "\n");
        }
      }
    }
}

function sendMessage($connection, string $message): void
{
    fwrite($connection, $message);
}

function receiveMessage($connection): string
{
    return fread($connection, 2048);
}

function quit($connection): void
{
    fclose($connection);
}
