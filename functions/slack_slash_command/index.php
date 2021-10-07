<?php
/**
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// [START functions_slack_setup]
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

// [END functions_slack_setup]

// [START functions_verify_webhook]
/**
 * Verify that the webhook request came from Slack.
 */
function isValidSlackWebhook(ServerRequestInterface $request): bool
{
    $SLACK_SECRET = getenv('SLACK_SECRET');

    // Check for headers
    $timestamp = $request->getHeaderLine('X-Slack-Request-Timestamp');
    $signature = $request->getHeaderLine('X-Slack-Signature');
    if (!$timestamp || !$signature) {
        return false;
    }

    // Compute signature
    $plaintext = sprintf('v0:%s:%s', $timestamp, $request->getBody());
    $hash = sprintf('v0=%s', hash_hmac('sha256', $plaintext, $SLACK_SECRET));

    return $hash === $signature;
}
// [END functions_verify_webhook]

// [START functions_slack_format]
/**
 * Format the Knowledge Graph API response into a richly formatted Slack message.
 */
function formatSlackMessage(Google_Service_Kgsearch_SearchResponse $kgResponse, string $query): string
{
    $responseJson = [
        'response_type' => 'in_channel',
        'text' => 'Query: ' . $query
    ];

    $entityList = $kgResponse['itemListElement'];

    // Extract the first entity from the result list, if any
    if (empty($entityList)) {
        $attachmentJson = ['text' => 'No results match your query...'];
        $responseJson['attachments'] = $attachmentJson;

        return json_encode($responseJson);
    }

    $entity = $entityList[0]['result'];

    // Construct Knowledge Graph response attachment
    $title = $entity['name'];
    if (isset($entity['description'])) {
        $title = $title . ' ' . $entity['description'];
    }
    $attachmentJson = ['title' => $title];

    if (isset($entity['detailedDescription'])) {
        $detailedDescJson = $entity['detailedDescription'];
        $attachmentJson = array_merge([
            'title_link' => $detailedDescJson[ 'url'],
            'text' => $detailedDescJson['articleBody'],
        ], $attachmentJson);
    }

    if (isset($entity['image'])) {
        $imageJson = $entity['image'];
        $attachmentJson['image_url'] = $imageJson['contentUrl'];
    }

    $responseJson['attachments'] = array($attachmentJson);

    return json_encode($responseJson);
}
// [END functions_slack_format]

// [START functions_slack_request]
/**
 * Send the user's search query to the Knowledge Graph API.
 */
function searchKnowledgeGraph(string $query): Google_Service_Kgsearch_SearchResponse
{
    $API_KEY = getenv('KG_API_KEY');

    $apiClient = new Google\Client();
    $apiClient->setDeveloperKey($API_KEY);

    $service = new Google_Service_Kgsearch($apiClient);

    $params = ['query' => $query];

    $kgResults = $service->entities->search($params);

    return $kgResults;
}
// [END functions_slack_request]

// [START functions_slack_search]
/**
 * Receive a Slash Command request from Slack.
 */
function receiveRequest(ServerRequestInterface $request): ResponseInterface
{
    // Validate request
    if ($request->getMethod() !== 'POST') {
        // [] = empty headers
        return new Response(405);
    }

    // Parse incoming URL-encoded requests from Slack
    // (Slack requests use the "application/x-www-form-urlencoded" format)
    $bodyStr = $request->getBody();
    parse_str($bodyStr, $bodyParams);

    if (!isset($bodyParams['text'])) {
        // [] = empty headers
        return new Response(400);
    }

    if (!isValidSlackWebhook($request, $bodyStr)) {
        // [] = empty headers
        return new Response(403);
    }

    $query = $bodyParams['text'];

    // Call knowledge graph API
    $kgResponse = searchKnowledgeGraph($query);

    // Format response to Slack
    // See https://api.slack.com/docs/message-formatting
    $formatted_message = formatSlackMessage($kgResponse, $query);

    return new Response(
        200,
        ['Content-Type' => 'application/json'],
        $formatted_message
    );
}
// [END functions_slack_search]
