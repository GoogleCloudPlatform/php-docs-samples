<?php
/**
 * Copyright 2018 Google Inc.
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

namespace Google\Cloud\Samples\AppEngine\Symfony;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\FileUtil;

trait DeploySymfonyTrait
{
    use AppEngineDeploymentTrait;
    use ExecuteCommandTrait;

    private static function createSymfonyProject()
    {
        $tmpDir = sys_get_temp_dir() . '/test-' . FileUtil::randomName(8);

        // install
        $demoVersion = 'symfony/symfony-demo:^1.5';
        $cmd = sprintf('composer create-project %s %s || true', $demoVersion, $tmpDir);
        $process = self::createProcess($cmd);
        $process->setTimeout(300); // 5 minutes

        self::executeProcess($process);
        self::setWorkingDirectory($tmpDir);

        // move app.yaml for the sample to the new symfony installation
        self::copyFiles(['app.yaml'], $tmpDir);

        // Remove the scripts from composer so they do not error out in the
        // Cloud Build environment.
        $json = json_decode(file_get_contents($tmpDir . '/composer.json'), true);
        unset($json['scripts']);
        file_put_contents($tmpDir . '/composer.json', json_encode($json, JSON_PRETTY_PRINT));

        // set the directory in gcloud and move there
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        return $tmpDir;
    }

    private static function updateKernelCacheAndLogDir($projectDir)
    {
        $kernelFile = $projectDir . '/src/Kernel.php';
        $kernelContents = file_get_contents($kernelFile);

        $newCode = <<<'EOF'

    public function getCacheDir()
    {
        if ($this->environment === 'prod') {
            return sys_get_temp_dir();
        }
        return parent::getCacheDir();
    }

    public function getLogDir()
    {
        if ($this->environment === 'prod') {
            return sys_get_temp_dir();
        }
        return parent::getLogDir();
    }
}
EOF;

        $newContents = preg_replace(
            '/^}$/m',
            $newCode,
            $kernelContents,
            1,
            $count
        );

        if ($count != 1) {
            throw new \UnexpectedValueException(
                'Failed to update Kernel Cache and Log Dir'
            );
        }

        file_put_contents($kernelFile, $newContents);
    }

    private static function copyFiles(array $files, $dir)
    {
        foreach ($files as $file) {
            $source = sprintf('%s/../%s', __DIR__, $file);
            $target = sprintf('%s/%s', $dir, $file);
            copy($source, $target);
        }
    }
}
