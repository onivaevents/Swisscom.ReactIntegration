<?php


namespace Swisscom\ReactIntegration\Eel;

use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Package\PackageManager;
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

    const SCRIPT_REGEX = '/<script[^>]*src="([^"]+)"/';

    /**
     * @param string $path in the form of resource://Your.Package/Public/Foo/Bar
     * @param string $fileType in the form of "js"
     * @return string
     * @throws \Neos\Flow\Package\Exception\UnknownPackageException
     * @throws \Neos\Flow\ResourceManagement\Exception
     */
    public function getCompiledReactScriptUris(string $path, string $fileType): array
    {
        list ($packageKey, $packageRelativePath) = $this->resourceManager->getPackageAndPathByPublicPath($path);
        $package = $this->packageManager->getPackage($packageKey);
        $folderToCheck = $package->getPackagePath() . 'Resources/Public/' . $packageRelativePath;
        $files = glob($folderToCheck . '/*.' . $fileType);

        $result = [];
        foreach ($files as $file) {
            $fileName = basename($file);
            $result[] = $this->resourceManager->getPublicPackageResourceUriByPath($path . '/' . $fileName);
        }

        return $result;
    }

    /**
     * @param string $reactDevelopmentServerUri http://localhost:3000
     * @param string $publicDevelopmentServerUri http://localhost:3000
     * @return string
     */
    public function getReactDevelopmentScriptUris($reactDevelopmentServerUri, $publicDevelopmentServerUri = null): array
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
            preg_match_all(self::SCRIPT_REGEX, $content, $matches, PREG_SET_ORDER);
            $output = [];
            foreach ($matches as $match) {
                $output[] = $publicDevelopmentServerUri . $match[1];
            }
            return $output;
        } catch (\Exception $exception) {
            return [];
        }

    }

    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
