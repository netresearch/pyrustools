#!/opt/phpfarm/inst/current-bin/php
<?php
declare(encoding = 'UTF-8');
/**
 * Display a list of
 * - channels which have installed packages
 * - installed packages
 * - commands to discover all channels
 * - commands to install all packages
 *
 * PHP Version 5
 *
 * usage:
 *    -d --discover  print all commands to discover current channels
 *    -i --install   print all commands to install current packages
 *    -c --channels  show list of channels with installed packages
 *    -p --packages  show list of installed packages
 *    -u --upgrades  print all commands to upgrade packages
 *    -x --pyrus-command [pyrus]
 *        pyrus commnd to use, f.e. 'pyrus' or 'pyrus-5.4.12'
 *    -h --help      show this help screen
 *
 * (C) 2013 Sebastian Mendel <sebastian.mendel@netresearch.de>
 *
 * @author Sebastian Mendel <sebastian.mendel@netresearch.de>
 */


$arOptions = array(
    'bDiscover' => array(
        '-d', '--discover',
    ),
    'bInstall' => array(
        '-i', '--install',
    ),
    'bChannels' => array(
        '-c', '--channels',
    ),
    'bPackages' => array(
        '-p', '--packages',
    ),
    'bUpgrades' => array(
        '-u', '--upgrades',
    ),
    'bVerbose' => array(
        '-v', '--verbose',
    ),
    'strPyrusCommand' => array(
        '-x', '--pyrus-command',
    ),
    'bHelp' => array(
        '-h', '--help',
    ),
);

$strPyrusCommand = 'pyrus';

if ($argc) {
    foreach (array_slice($argv, 1) as $strV) {
        if (substr($strV, 0, 1) === '-') {
            if (substr($strV, 1, 1) === '-') {
                setOption($strV);
            } else {
                if (strlen($strV) > 2) {
                    $arOptionSwitches = str_split(substr($strV, 1), 1);
                    foreach ($arOptionSwitches as $strOptionSwitch) {
                        $strOptionSwitch = '-' . $strOptionSwitch;
                        setOption($strOptionSwitch);
                    }
                } else {
                    setOption($strV);
                }
            }
        } else {
            setOption(null, $strV);
        }
    }
}


if (! empty($bHelp)) {

    $strFile = file_get_contents(__FILE__);
    $arLines = explode("\n", $strFile);
    foreach ($arLines as $strLine) {
        if (0 === strpos($strLine, ' *')) {
            echo "\t" . substr($strLine, 3) . "\n";
        }
        if (0 === strpos($strLine, ' */')) {
            exit;
        }
    }
    exit;
}


$strList = x("$strPyrusCommand list-packages");

$arList = explode("\n", $strList);

foreach ($arList as $strEntry) {

    // [channel packages.zendframework.com]:
    if (preg_match('/^\[channel ([^\]]+)\]:$/', $strEntry, $arChannel)) {
        $strCurrentChannel = $arChannel[1];
    }

    if (empty($strCurrentChannel)) {
        continue;
    }

    // find PHP_CodeCoverage 1.2.6 stable
    // but not (no packages installed in channel __uri)
    if (preg_match('/^([0-9a-z_]+) ([0-9a-z.]+) [a-z]+$/i', $strEntry, $arPackage)) {
        $arChannels[$strCurrentChannel][$arPackage[1]]
            = $arPackage[1] . '-' . $arPackage[2];
        $arPackages[]
            = $strCurrentChannel . '/' . $arPackage[1] . '-' . $arPackage[2];
    }
}

if (! empty($pChannels)) {
    echo "\n" . 'Channels:' . "\n";
    echo '=========' . "\n";
    foreach ($arChannels as $strChannel => $arChannel) {
        echo $strChannel . "\n";
    }
}

if (! empty($bDiscover)) {
    foreach ($arChannels as $strChannel => $arChannel) {
        echo $strPyrusCommand . ' channel-discover ' . $strChannel . "\n";
    }
}

if (! empty($bPackages)) {
    echo "\n" . 'Packages:' . "\n";
    echo '=========' . "\n";
    foreach ($arPackages as $strPackage) {
        echo $strPackage . "\n";
    }
}

if (! empty($bInstall)) {
    foreach ($arPackages as $strPackage) {
        echo $strPyrusCommand . ' install -f ' . $strPackage . "\n";
    }
}

if (! empty($bUpgrades)) {

    $arPackagesUpgrades = $arChannelUpgrades = array();
    foreach ($arChannels as $strChannel => $arChannel) {
        $strUpgradePackages = x(
            $strPyrusCommand . ' remote-list ' . $strChannel
        );

        $arUpgradePackages = explode("\n", $strUpgradePackages);

        $bAppend = false;
        $arPackage = array(
            'name' => '',
            'version' => '',
        );

        foreach ($arUpgradePackages as $strUpgradePackage) {

            // !  Archive_Tar                1.3.11la\ Tar file management class
            //                               ngervers\
            //                               string
            // 01234567890123456789012345678901234567890123456789012345678901234
            // 0         1         2         3         4         5         6
            if ($bAppend || preg_match('/^!  /i', $strUpgradePackage)) {
                $arPackage['name'] .= trim(substr($strUpgradePackage, 3, 26));
                $arPackage['version'] .= trim(substr($strUpgradePackage, 30, 9));

                if (substr($arPackage['name'], -1) === '\\'
                    || substr($arPackage['version'], -1) === '\\'
                ) {
                    $arPackage['name'] = trim($arPackage['name'], ' \\');
                    $arPackage['version'] = trim($arPackage['version'], ' \\');
                    $bAppend = true;
                } else {
                    $arChannelUpgrades[$strChannel][$arPackage['name']]
                        = $arPackage['name'] . '-' . $arPackage['version'];
                    $arPackagesUpgrades[]
                        = $strChannel . '/' . $arPackage['name'] . '-' . $arPackage['version'];

                    $arPackage = array(
                        'name' => '',
                        'version' => '',
                    );
                    $bAppend = false;
                }
            }
        }
    }

    foreach ($arPackagesUpgrades as $strPackageUpgrade) {
        echo $strPyrusCommand . ' upgrade -f ' . $strPackageUpgrade . "\n";
    }
}

/**
 * Executes given command.
 *
 * @param string $strCommand Command to execute
 *
 * @return string command result
 */
function x($strCommand)
{
    if (! empty($GLOBALS['bVerbose'])) {
        echo 'exec: "' . $strCommand . '"' . "\n";
    }
    return shell_exec($strCommand);
}

/**
 * Set an option. usualy from cli arguments.
 *
 * @param string $strSwitch Used command argument switch
 * @param string $mValue    Value for option to set
 */
function setOption($strSwitch, $mValue = true)
{
    static $strLastOption = null;

    foreach ($GLOBALS['arOptions'] as $strOption => $arOptionSwitches) {
        if (null === $strSwitch && null !== $strLastOption) {
            $GLOBALS[$strLastOption] = $mValue;
        } elseif (in_array($strSwitch, $arOptionSwitches)) {
            $GLOBALS[$strOption] = $mValue;
            $strLastOption = $strOption;
        }
    }
}
?>