# Google Cloud CDN Sign URL

PHP implementation of [`gcloud compute sign-url`](https://cloud.google.com/sdk/gcloud/reference/compute/sign-url) based on [Google Cloud CDN Documentation](https://cloud.google.com/cdn/docs/using-signed-urls#signing_urls).
The provided file includes implementation of base64url encode and decode functions based on [RFC4648 Section 5](https://tools.ietf.org/html/rfc4648#section-5).

## Usage

```php
require_once 'signUrl.php';
$base64url_key = 'wpLL7f4VB9RNe_WI0BBGmA=='; // head -c 16 /dev/urandom | base64 | tr +/ -_
$signed_url = signUrl('https://example.com/foo', 'my-key', $base64url_key, time() + 1800);
echo $signed_url;
```
