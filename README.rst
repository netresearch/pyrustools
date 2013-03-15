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
