<?php

namespace Oro\Bundle\ApiBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Loader\FileLoader;

trait RestLoaderTrait
{
    /**
     * Import & return routes collection from a resource.
     *
     * @param LoaderInterface $loader      The Loader
     * @param mixed           $resource    A Resource
     * @param array           $parents     Array of parent resources names
     * @param string          $routePrefix Current routes prefix
     * @param string          $namePrefix  Routes names prefix
     * @param string          $type        The resource type
     * @param string          $currentDir  Current directory of the loader
     *
     * @return RouteCollection A RouteCollection instance
     */
    protected function importResource(
        LoaderInterface $loader,
        $resource,
        array $parents = [],
        $routePrefix = null,
        $namePrefix = null,
        $type = null,
        $currentDir = null
    ) {
        $loader = $loader->resolve($resource, $type);

        if ($loader instanceof FileLoader && null !== $currentDir) {
            $resource = $loader->getLocator()->locate($resource, $currentDir);
        } elseif ($loader instanceof RestRouteLoader) {
            return $loader->load($resource, $type, $parents, $routePrefix, $namePrefix);
        }

        return $loader->load($resource, $type);
    }

    /**
     * Returns the full class name for the first class in the file.
     */
    protected function findClassInFile($file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));

        if (1 === \count($tokens) && \T_INLINE_HTML === $tokens[0][0]) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain PHP code. Did you forgot to add the "<?php" start tag at the beginning of the file?', $file));
        }

        $nsTokens = [\T_NS_SEPARATOR => true, \T_STRING => true];
        if (\defined('T_NAME_QUALIFIED')) {
            $nsTokens[\T_NAME_QUALIFIED] = true;
        }
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];
            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && \T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }

            if (true === $namespace && isset($nsTokens[$token[0]])) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1], $nsTokens[$tokens[$i][0]])) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if (\T_CLASS === $token[0]) {
                // Skip usage of ::class constant and anonymous classes
                $skipClassToken = false;
                for ($j = $i - 1; $j > 0; --$j) {
                    if (!isset($tokens[$j][1])) {
                        if ('(' === $tokens[$j] || ',' === $tokens[$j]) {
                            $skipClassToken = true;
                        }
                        break;
                    }

                    if (\T_DOUBLE_COLON === $tokens[$j][0] || \T_NEW === $tokens[$j][0]) {
                        $skipClassToken = true;
                        break;
                    } elseif (!\in_array($tokens[$j][0], [\T_WHITESPACE, \T_DOC_COMMENT, \T_COMMENT])) {
                        break;
                    }
                }

                if (!$skipClassToken) {
                    $class = true;
                }
            }

            if (\T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
