<?php
/**
 * Copyright 2016 Google Inc.
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

namespace Google\Cloud\Samples\Appengine\Endpoints;

use Google\Auth\OAuth2;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class EndpointsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('make-request')
            ->setDescription('Send in a request to endpoints')
            ->addArgument(
                'host',
                InputArgument::REQUIRED,
                'Your API host, e.g. https://your-project.appspot.com.'
            )
            ->addArgument(
                'api_key',
                InputArgument::REQUIRED,
                'Your API key.'
            )
            ->addArgument(
                'credentials',
                InputArgument::OPTIONAL,
                'The path to your credentials file. This can be service account credentials, client secrets, or omitted.'
            )
            ->addOption(
                'message',
                'm',
                InputOption::VALUE_REQUIRED,
                'The message to send in',
                'TEST MESSAGE (change this with -m)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api_key = $input->getArgument('api_key');
        $host = $input->getArgument('host');
        $message = $input->getOption('message');

        $http = new HttpClient(['base_uri' => $host]);
        $headers = [];
        $body = null;

        if ($credentials = $input->getArgument('credentials')) {
            if (!file_exists($credentials)) {
                throw new InvalidArgumentException('file does not exist');
            }
            if (!$config = json_decode(file_get_contents($credentials), true)) {
                throw new LogicException('invalid json for auth config');
            }

            $oauth = new OAuth2([
                'issuer'    => 'jwt-client.endpoints.sample.google.com',
                'audience'  => 'echo.endpoints.sample.google.com',
                'scope'     => 'email',
                'authorizationUri' => 'https://accounts.google.com/o/oauth2/auth',
                'tokenCredentialUri' => 'https://www.googleapis.com/oauth2/v4/token',
            ]);

            if (isset($config['type']) && $config['type'] == 'service_account') {
                // return the "jwt" info from the request
                $method = 'GET';
                $path = '/auth/info/googlejwt';

                $oauth->setSub('123456');
                $oauth->setSigningKey($config['private_key']);
                $oauth->setSigningAlgorithm('RS256');
                $oauth->setClientId($config['client_id']);
                $jwt = $oauth->toJwt();

                $headers['Authorization'] = sprintf('Bearer %s', $jwt);
            } else {
                // return the "idtoken" info from the request
                $method = 'GET';
                $path = '/auth/info/googleidtoken';

                // open the URL
                $oauth->setClientId($config['installed']['client_id']);
                $oauth->setClientSecret($config['installed']['client_secret']);
                $oauth->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
                $authUrl = $oauth->buildFullAuthorizationUri(['access_type'  => 'offline']);
                `open '$authUrl'`;

                // prompt for the auth code
                $q = new Question('Please enter the authorization code:');
                $helper = new QuestionHelper();
                $authCode = $helper->ask($input, $output, $q);
                $oauth->setCode($authCode);

                $token = $oauth->fetchAuthToken();
                if (empty($token['id_token'])) {
                    return $output->writeln("<error>unable to retrieve ID token</error>");
                }
                $headers['Authorization'] = sprintf('Bearer %s', $token['id_token']);
            }
        } else {
            // return just the message we sent in
            $method = 'POST';
            $path = '/echo';
            $body = json_encode([ 'message' => $message ]);
            $headers['Content-Type'] = 'application/json';
        }

        $output->writeln(sprintf('requesting "%s"...', $path));

        $response = $http->request($method, $path, [
            'query' => ['key' => $api_key],
            'body'  => $body,
            'headers' => $headers
        ]);

        $output->writeln((string) $response->getBody());
    }
}
