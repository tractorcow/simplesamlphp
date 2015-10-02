<?php
namespace SimpleSAML\Utils;

/**
 * Utility class for SimpleSAMLphp configuration management and manipulation.
 *
 * @package SimpleSAMLphp
 */
class Config
{

    /**
     * Resolves a path that may be relative to the cert-directory.
     *
     * @param string $path The (possibly relative) path to the file.
     *
     * @return string  The file path.
     * @throws \InvalidArgumentException If $path is not a string.
     *
     * @author Olav Morken, UNINETT AS <olav.morken@uninett.no>
     */
    public static function getCertPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Invalid input parameters.');
        }

        $globalConfig = \SimpleSAML_Configuration::getInstance();
        $base = $globalConfig->getPathValue('certdir', 'cert/');
        return System::resolvePath($path, $base);
    }


    /**
     * Retrieve the secret salt.
     *
     * This function retrieves the value which is configured as the secret salt. It will check that the value exists
     * and is set to a non-default value. If it isn't, an exception will be thrown.
     *
     * The secret salt can be used as a component in hash functions, to make it difficult to test all possible values
     * in order to retrieve the original value. It can also be used as a simple method for signing data, by hashing the
     * data together with the salt.
     *
     * @return string The secret salt.
     * @throws \InvalidArgumentException If the secret salt hasn't been configured.
     *
     * @author Olav Morken, UNINETT AS <olav.morken@uninett.no>
     */
    public static function getSecretSalt()
    {
        $secretSalt = \SimpleSAML_Configuration::getInstance()->getString('secretsalt');
        if ($secretSalt === 'defaultsecretsalt') {
            throw new \InvalidArgumentException('The "secretsalt" configuration option must be set to a secret value.');
        }

        return $secretSalt;
    }

    /**
     * Returns the path to the config dir
     *
     * If the SIMPLESAMLPHP_CONFIG_DIR environment variable has been set, it takes precedence over the default
     * $simplesamldir/config directory.
     *
     * @return string The path to the configuration directory.
     */
    public static function getConfigDir()
    {
        // Load _ss_environment.php. Code copied from silverstripe-framework/core/Constants.php
		// Only load if the constant `SS_ENVIRONMENT_FILE` isn't already defined, as we can either run in or outside SS

		if(!defined('SS_ENVIRONMENT_FILE')) {
			//define the name of the environment file
			$envFile = '_ss_environment.php';
			//define the dirs to start scanning from (have to add the trailing slash)
			// we're going to check the realpath AND the path as the script sees it
			$dirsToCheck = array(
				realpath('.'),
				dirname($_SERVER['SCRIPT_FILENAME'])
			);
			//if they are the same, remove one of them
			if ($dirsToCheck[0] == $dirsToCheck[1]) {
				unset($dirsToCheck[1]);
			}
			foreach ($dirsToCheck as $dir) {
				//check this dir and every parent dir (until we hit the base of the drive)
				// or until we hit a dir we can't read
				while(true) {
					//if it's readable, go ahead
					if (@is_readable($dir)) {
						//if the file exists, then we include it, set relevant vars and break out
						if (file_exists($dir . DIRECTORY_SEPARATOR . $envFile)) {
							define('SS_ENVIRONMENT_FILE', $dir . DIRECTORY_SEPARATOR . $envFile);
							include_once(SS_ENVIRONMENT_FILE);
							//break out of BOTH loops because we found the $envFile
							break(2);
						}
					}
					else {
						//break out of the while loop, we can't read the dir
						break;
					}
					if (dirname($dir) == $dir) {
						// here we need to check that the path of the last dir and the next one are
						// not the same, if they are, we have hit the root of the drive
						break;
					}
					//go up a directory
					$dir = dirname($dir);
				}
			}
		}

		$configDir = defined('REALME_CONFIG_DIR') ? constant('REALME_CONFIG_DIR') : null;

		if(is_null($configDir) || !is_dir($configDir)) {
			throw new \InvalidArgumentException(
				sprintf(
					'Config directory specified by _ss_environment variable REALME_CONFIG_DIR is not a ' .
					'directory.  Given: "%s"',
					$configDir
				)
			);
		}

		return $configDir;
	}
}
