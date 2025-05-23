<?php

declare(strict_types=1);

namespace Swisscom\ReactIntegration\Eel;

use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Package\Exception\UnknownPackageException;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\ResourceManagement\Exception;
use Neos\Flow\ResourceManagement\ResourceManager;

class ReactIntegrationHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var PackageManager
     */
    protected $packageManager;

    const string SCRIPT_REGEX = '/<script[^>]*src="([^"]+)"/';

    /**
     * @param string $path in the form of resource://Your.Package/Public/Foo/Bar
     * @param string $fileType in the form of "js"
     * @return string[]
     * @throws UnknownPackageException
     * @throws Exception
     */
    public function getCompiledReactScriptUris(string $path, string $fileType): array
    {
        list ($packageKey, $packageRelativePath) = $this->resourceManager->getPackageAndPathByPublicPath($path);
        if (!is_string($packageKey) || !is_string($packageRelativePath)) {
            return [];
        }

        $package = $this->packageManager->getPackage($packageKey);
        $folderToCheck = $package->getPackagePath() . 'Resources/Public/' . $packageRelativePath;

        $result = [];
        if ($files = glob($folderToCheck . '/*.' . $fileType)) {
            foreach ($files as $file) {
                $fileName = basename($file);
                $result[] = $this->resourceManager->getPublicPackageResourceUriByPath($path . '/' . $fileName);
            }
        }

        return $result;
    }

    /**
     * @param string $reactDevelopmentServerUri http://localhost:3000
     * @param string|null $publicDevelopmentServerUri http://localhost:3000
     * @return string[]
     */
    public function getReactDevelopmentScriptUris(string $reactDevelopmentServerUri, ?string $publicDevelopmentServerUri = null): array
    {
        if ($publicDevelopmentServerUri === null) {
            $publicDevelopmentServerUri = $reactDevelopmentServerUri;
        }
        try {
            $browser = new Browser();
            $browser->setRequestEngine(new CurlEngine());
            $response = $browser->request(new Uri($reactDevelopmentServerUri));
            $content = $response->getBody();
            $matches = null;
            preg_match_all(self::SCRIPT_REGEX, (string)$content, $matches, PREG_SET_ORDER);
            $output = [];
            foreach ($matches as $match) {
                $output[] = $publicDevelopmentServerUri . $match[1];
            }
            return $output;
        } catch (\Exception) {
            return [];
        }

    }

    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
