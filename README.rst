pyrustools
----------

Manage channels and packes for pyrus installation. 
Help transfering packages from one pyrus installation to another.

Useful in conjunction with phpfarm.


Informativ
..........

List all channels which have installed packages::

 pyruslist -c

List all installed pagackes::

 pyruslist -p

Export
......

Print all commands to discover current channels::

 pyruslist -d

Print all commands to install current packages::

 pyruslist -i

Managing
........

Print all commands to upgrade packages::

 pyruslist -u

Example
.......

Display all commands to import complete pyrus installation on another machine or
php/pyrus installation::

 $ ./pyruslist.php -d -i -x pyrus-5.4.13
 pyrus-5.4.13 channel-discover pear.phpunit.de
 pyrus-5.4.13 channel-discover pear.php.net
 pyrus-5.4.13 install -f pear.phpdoc.org/phpDocumentor-2.0.0a12
 pyrus-5.4.13 install -f pear.survivethedeepend.com/Mockery-0.7.2
 pyrus-5.4.13 install -f pear.phpunit.de/File_Iterator-1.3.3
 pyrus-5.4.13 install -f pear.phpunit.de/FinderFacade-1.0.6
 pyrus-5.4.13 install -f pear.phpunit.de/PHPUnit-3.7.14
 pyrus-5.4.13 install -f pear.phpunit.de/PHPUnit_MockObject-1.2.3
 pyrus-5.4.13 install -f pear.phpunit.de/PHP_CodeCoverage-1.2.8
 pyrus-5.4.13 install -f pear.phpunit.de/PHP_Invoker-1.1.2
 pyrus-5.4.13 install -f pear.phpunit.de/PHP_Timer-1.0.4
 pyrus-5.4.13 install -f pear.phpunit.de/PHP_TokenStream-1.1.5
 pyrus-5.4.13 install -f pear.phpunit.de/Text_Template-1.1.4
 pyrus-5.4.13 install -f pear.phpunit.de/phpcpd-1.4.0
 pyrus-5.4.13 install -f pear.php.net/Archive_Tar-1.3.10
 pyrus-5.4.13 install -f pear.php.net/Auth-1.6.4
 pyrus-5.4.13 install -f pear.php.net/Config-1.10.12
 pyrus-5.4.13 install -f pear.php.net/Console_CommandLine-1.2.0
 pyrus-5.4.13 install -f pear.php.net/Console_Getopt-1.3.1
 pyrus-5.4.13 install -f pear.php.net/DB-1.7.14
 pyrus-5.4.13 install -f pear.php.net/HTTP_Request2-2.1.1
 pyrus-5.4.13 install -f pear.php.net/Log-1.12.7
 pyrus-5.4.13 install -f pear.php.net/Net_LDAP2-2.0.12
 pyrus-5.4.13 install -f pear.php.net/Net_URL2-2.0.0
 pyrus-5.4.13 install -f pear.php.net/PEAR-1.9.4
 pyrus-5.4.13 install -f pear.php.net/PHP_Beautifier-0.1.15
 pyrus-5.4.13 install -f pear.php.net/PHP_CodeSniffer-1.4.2
 pyrus-5.4.13 install -f pear.php.net/Services_Libravatar-0.2.2
 pyrus-5.4.13 install -f pear.php.net/Structures_Graph-1.0.4
 pyrus-5.4.13 install -f pear.php.net/System_Folders-1.0.5
 pyrus-5.4.13 install -f pear.php.net/VersionControl_Git-0.4.4
 pyrus-5.4.13 install -f pear.php.net/VersionControl_SVN-0.5.1
 pyrus-5.4.13 install -f pear.php.net/XML_Parser-1.3.4
 pyrus-5.4.13 install -f pear.php.net/XML_Util-1.2.1


Complete command reference
..........................

::

   -d --discover  print all commands to discover current channels
   -i --install   print all commands to install current packages
   -c --channels  show list of channels with installed packages
   -p --packages  show list of installed packages
   -u --upgrades  print all commands to upgrade packages
   -x --pyrus-command [pyrus]
       pyrus commnd to use, f.e. 'pyrus' or 'pyrus-5.4.12'
   -h --help      show this help screen
	
(C) 2013 Sebastian Mendel <sebastian.mendel@netresearch.de>
