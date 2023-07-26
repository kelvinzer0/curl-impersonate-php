# Curl-Impersonate-PHP

Curl-Impersonate-PHP is a library that allows the execution of HTTP requests using cURL within the PHP environment with the ability to emulate the behavior of four major browsers (Chrome, Firefox, Safari, and Microsoft Edge).

## Description

Curl-Impersonate-PHP is an implementation of the original project Curl-Impersonate, available at [https://github.com/lwthiker/curl-impersonate](https://github.com/lwthiker/curl-impersonate), which introduces a specialized cURL build that can mimic the behavior of four major browsers. By using Curl-Impersonate-PHP, you can make HTTP requests from PHP using cURL but with headers and behavior that resemble Chrome, Firefox, Safari, or Microsoft Edge.

## Key Features

- Support for making HTTP requests through cURL with headers and behavior emulating Chrome, Firefox, Safari, and Microsoft Edge.
- Full control over cURL options such as the target URL, HTTP method, request data, headers, and more through the `setopt` function.
- Ability to access the execution results of the request in the form of a cURL command, standard output, or streaming.

## Installation

You can install Curl-Impersonate-PHP using Composer. Run the following command in your project directory:

```bash
composer require kelvinzer0/curl-impersonate-php
```

## Usage

Below is an example of using Curl-Impersonate-PHP to make an HTTP request while emulating the behavior of a browser:

```php
$curl = new CurlImpersonate();
$curl->setopt(CURLCMDOPT_URL, 'https://example.com/');
$curl->setopt(CURLCMDOPT_METHOD, 'GET');
$curl->setopt(CURLCMDOPT_HEADER, false);
$curl->setopt(CURLCMDOPT_ENGINE, "/Users/qindexmedia/Downloads/curl-impersonate-v0.5.4.x86_64-macos/curl_safari15_3");
$response = $curl->execStandard();
echo $response;
$curl->closeStream();
```

Be sure to replace the value of the `CURLCMDOPT_URL` option with the appropriate target URL and set the browser impersonation according to your needs.

## `setopt` Function

The `setopt` function is used to set options in the HTTP request that will be executed using cURL. Here is a list of options supported by the `setopt` function:

| Option                         | Description                                                                                             |
|--------------------------------|---------------------------------------------------------------------------------------------------------|
| `CURLCMDOPT_URL`               | Sets the target URL for the HTTP request.                                                               |
| `CURLCMDOPT_METHOD`            | Sets the HTTP method to be used (e.g., GET, POST, PUT, etc.).                                            |
| `CURLCMDOPT_POSTFIELDS`        | Sets the data to be sent as the request body (can be in the form of an array or object, will be converted to JSON). |
| `CURLCMDOPT_HTTP_HEADERS`      | Sets the HTTP headers in the form of an array for the request.                                          |
| `CURLCMDOPT_HEADER`            | Sets whether the response will include headers or not (boolean).                                        |
| `CURLCMDOPT_ENGINE`            | Sets the cURL engine to be used for request execution (e.g., "curl", "wget", etc.).                    |

## Streaming Usage

In addition to supporting getting the standard output of HTTP request execution, Curl-Impersonate-PHP also provides the ability to stream (retrieve data in chunks) the response from an ongoing HTTP request. You can use the streaming feature with the following steps:

1. **Enable Streaming**: Call the `execStream` function to enable streaming before starting the HTTP request:

```php
$curl = new CurlImpersonate();
$curl->setopt(CURLCMDOPT_URL, 'https://example.com/');
$curl->setopt(CURLCMDOPT_METHOD, 'GET');
$curl->setopt(CURLCMDOPT_ENGINE, "/Users/qindexmedia/Downloads/curl-impersonate-v0.5.4.x86_64-macos/curl_safari15_3");
$curl->execStream();
```

2. **Retrieve Data in Chunks**: You can use the `readStream` function to retrieve response data in chunks of the specified size:

```php
$chunkSize = 4096; // Size of the data chunks to be retrieved (in bytes)
while ($data = $curl->readStream($chunkSize)) {
    echo $data;
    // Process the response data here
}
```

3. **Close Streaming**: After you finish using streaming, be sure to close it by calling the `closeStream` function:

```php
$curl->closeStream();
```

## Example of Streaming Usage

Here's a complete example of using streaming in Curl-Impersonate-PHP:

```php
$curl = new CurlImpersonate();
$curl->setopt(CURLCMDOPT_URL, 'https://example.com/');
$curl->setopt(CURLCMDOPT_METHOD, 'GET');
$curl->setopt(CURLCMDOPT_ENGINE, "/Users/qindexmedia/Downloads/curl-impersonate-v0.5.4.x86_64-macos/curl_safari15_3");
$curl->execStream();

$chunkSize = 4096; // Size of the data chunks to be retrieved (in bytes)
while ($data = $curl->readStream($chunkSize)) {
    echo $data;
    // Process the response data here
}

$curl->closeStream();
```

Be sure to replace the value of `CURLCMDOPT_URL` with the appropriate target URL and set the browser impersonation according to your needs. Streaming is particularly useful for handling large responses or responses that need to be processed in specific chunks sequentially.

## Contributions

If you would like to contribute to Curl-Impersonate-PHP, we greatly appreciate your contributions. Please open a new issue or submit a pull request on our GitHub repository.

## License

Curl-Impersonate-PHP is licensed under the [MIT License](LICENSE), which means you are free to use, modify, and distribute this library according to the terms of the license.
