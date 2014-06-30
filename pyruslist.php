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
 *    -s --pyrus-source [pyrus]
 *        pyrus command to retrive package information, f.e. 'pyrus' or 'pyrus-5.4.12'
 *    -t --pyrus-target [pyrus]
 *        pyrus command to use wiht printed out commands, f.e. 'pyrus-5.4.12'
 *    -h --help      show this help screen
 *    -X --execute   executes the commands the otherwise printed commends
 *
 * Exporting all commands required to install packages of given pyrus
 * installation on another pyrus installation:
 *
 * $ pyruslist.php -i -s pyrus_5.4.14
 *
 * Importing packages from one pyrus installation to another:
 *
 * $ pyruslist.php -X -s pyrus_5.4.14 -t pyrus_5.4.15
 *
 * (C) 2013 Sebastian Mendel <sebastian.mendel@netresearch.de>
 *
 * @category Pyrus
 * @package  Netresearch_Phpfarm
 * @author   Sebastian Mendel <sebastian.mendel@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL
 * @version  0.1.0
 * @link     http://netresearch.de/
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
    'strPyrusSource' => array(
        '-s', '--pyrus-source',
    ),
    'strPyrusTarget' => array(
        '-t', '--pyrus-target',
    ),
    'bExec' => array(
        '-X', '--execute',
    ),
    'bHelp' => array(
        '-h', '--help',
    ),
);

$strPyrusSource = 'pyrus';

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

if (empty($strPyrusTarget)) {
    $strPyrusTarget = $strPyrusSource;
}

if (! empty($bHelp)) {
    $strFile = file_get_contents(__FILE__);
    $arLines = explode("\n", $strFile);
    foreach ($arLines as $strLine) {
        if (0 === strpos($strLine, ' *')) {
            outL("\t" . substr($strLine, 3));
        }
        if (0 === strpos($strLine, ' */')) {
            exit;
        }
    }
    exit;
}


$strList = x("$strPyrusSource list-packages");

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

if (! empty($bChannels)) {
    outH('Channels');
    foreach ($arChannels as $strChannel => $arChannel) {
        outL($strChannel);
    }
}

if (! empty($bDiscover) && ! empty($arChannels)) {
    foreach ($arChannels as $strChannel => $arChannel) {
        $strCommand = $strPyrusTarget . ' channel-discover ' . $strChannel;

        if (! empty($bExec)) {
            x($strCommand);
        } else {
            outL($strCommand);
        }
    }
}

if (! empty($bPackages) && ! empty($arPackages)) {
    outH('Packages');
    foreach ($arPackages as $strPackage) {
        outL($strPackage);
    }
}

if (! empty($bInstall) && ! empty($arPackages)) {
    foreach ($arPackages as $strPackage) {
        $strCommand = $strPyrusTarget . ' install -f ' . $strPackage;

        if (! empty($bExec)) {
            x($strCommand);
        } else {
            outL($strCommand);
        }

        if (0 === strpos($strPackage, 'pecl.php.net/')) {
            $strCommand = $strPyrusTarget . ' build ' . $strPackage;

            if (! empty($bExec)) {
                x($strCommand);
            } else {
                outL($strCommand);
            }
        }
    }
}

if (! empty($bUpgrades)) {

    $arPackagesUpgrades = $arChannelUpgrades = array();
    foreach ($arChannels as $strChannel => $arChannel) {
        $strUpgradePackages = x(
            $strPyrusSource . ' remote-list ' . $strChannel
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
                        = $strChannel . '/' . $arPackage['name']
                        . '-' . $arPackage['version'];

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
        $strCommand = $strPyrusSource . ' upgrade -f ' . $strPackageUpgrade;

        if (! empty($bExec)) {
            x($strCommand);
        } else {
            outL($strCommand);
        }
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
    L('exec: "' . $strCommand . '"');
    return shell_exec($strCommand);
}

/**
 * Set an option. usually from CLI arguments.
 *
 * @param string $strSwitch Used command argument switch
 * @param string $mValue    Value for option to set
 *
 * @return void
 */
function setOption($strSwitch, $mValue = true)
{
    static $strLastOption = null;

    if (null === $strSwitch && null !== $strLastOption) {
        L('option: "' . $strLastOption . '" = ' . (string) $mValue);
        $GLOBALS[$strLastOption] = $mValue;
        $strLastOption = null;
        return;
    }
    $strLastOption = null;

    $bValid = false;

    foreach ($GLOBALS['arOptions'] as $strOption => $arOptionSwitches) {
        if (in_array($strSwitch, $arOptionSwitches)) {
            L('option: "' . $strOption . '" = ' . (string) $mValue);
            $GLOBALS[$strOption] = $mValue;
            $strLastOption = $strOption;
            $bValid = true;
            break;
        }
    }

    if (! $bValid) {
        outL('Unknown option: "' . $strSwitch . '"');
    }
}



/**
 * Print headline.
 *
 * @param string $strHeadLine Headline content.
 *
 * @return void
 */
function outH($strHeadLine)
{
    outL("\n" . $strHeadLine);
    outL(str_repeat('=', strlen($strHeadLine)));
}



/**
 * Print single line of text.
 *
 * @param string $strLine Text line content.
 *
 * @return void
 */
function outL($strLine)
{
    echo $strLine . "\n";
}



/**
 * Log messages.
 *
 * Message is printed to stdout if verbose is true.
 *
 * @param string $strLine Message to log
 *
 * @return void
 */
function L($strLine)
{
    if (! empty($GLOBALS['bVerbose'])) {
        outL($strLine);
    }
}
?>