<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\exceptions\InvalidArgumentException;

/**
 * Aliases service.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Aliases implements AliasesInterface
{
    protected $aliases = [];

    /**
     * Magic setter to enable simple aliases configuration.
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->setAlias($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : $this->getAlias($path);
            if (!isset($this->aliases[$root])) {
                if ($pos === false) {
                    $this->aliases[$root] = $path;
                } else {
                    $this->aliases[$root] = [$alias => $path];
                }
            } elseif (is_string($this->aliases[$root])) {
                if ($pos === false) {
                    $this->aliases[$root] = $path;
                } else {
                    $this->aliases[$root] = [
                        $alias => $path,
                        $root => $this->aliases[$root],
                    ];
                }
            } else {
                $this->aliases[$root][$alias] = $path;
                krsort($this->aliases[$root]);
            }
        } elseif (isset($this->aliases[$root])) {
            if (is_array($this->aliases[$root])) {
                unset($this->aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset($this->aliases[$root]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias($alias, $throwException = true)
    {
        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }

        $result = $this->findAlias($alias);

        if (is_array($result)) {
            return $result['path'];
        }

        if ($throwException) {
            throw new InvalidArgumentException("Invalid path alias: $alias");
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootAlias($alias)
    {
        $result = $this->findAlias($alias);
        if (is_array($result)) {
            $result = $result['root'];
        }
        return $result;
    }

    /**
     * @param string $alias
     * @return array|bool
     */
    protected function findAlias(string $alias)
    {
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset($this->aliases[$root])) {
            if (is_string($this->aliases[$root])) {
                return ['root' => $root, 'path' => $pos === false ? $this->aliases[$root] : $this->aliases[$root] . substr($alias, $pos)];
            }

            foreach ($this->aliases[$root] as $name => $path) {
                if (strpos($alias . '/', $name . '/') === 0) {
                    return ['root' => $name, 'path' => $path . substr($alias, strlen($name))];
                }
            }
        }

        return false;
    }
}
