# BVCP Application (Bhyve Virtual-Machine Control Panel)

**Current Version: 1.0-Release Candidate**

## Introduction
This is a personal project from the author of nPulse.net, Viktor Hlavaji (DaVieS).
nPulse.net is always willing to share knowledge and resources with others, and I have 10+ experience of making industry-class / enterprise-class softwares.

The first publicly available release for professional use, it does have a Graphical user Interface via webinterface and also a CLI and an API.
The software provides webinterface to manage Virtual Machines.

The software is uses our framework "Kinga Framework" which is used in various enterprise-class products.
Mostly written in C/C++.
The software has its components:
- Frontend (Web Interface)
- Backend (Worker)
- Helper 
- Utils

Please refer to the website for more informations: [bhyve.npulse.net](https://bhyve.npulse.net)

## ScreenShots
<img src="screenshots/scr599.png" width="350" alt="API CLI Interface"> <img src="screenshots/scr597.png" height="251" alt="Login Area">
<img src="screenshots/scr592.png" width="420" alt="Running Windows VM"> <img src="screenshots/scr590.png" width="420" alt="GUI Looklike">
<img src="screenshots/scr589.png" width="415" alt="Running Linux VM"> <img src="screenshots/scr587.png" width="415" alt="Dashboard">

## Bhyve
Bhyve is a supervisor of FreeBSD, and this software requires and only works on FreeBSD 12+.

## License: Community Free-Of-Charge Edition
- You can download, install and use the BVCP Application without any charges and limitations.
- You can not modify, disassemble, resell, redistribute, sell, to rent, etc..
- You are allowed to upload screenshots and videos from the application itself in purpose of documentation, tutorial, how-to
- Please refer to the LICENSE for more infomrations.

## Installation
### Minimum Requirements
- Latest or at least a FreeBSD 12 installed onto your target machine with virtualisation capable amd64 architecture.
- Minimum 250MB of free space on /var/lib for the binaries
- Network interface

### Prerequisites
- Ensure that you do not have bridge and tap interfaces between number 300 - 900, (tap303, bridge550), BVCP will be use between 300 and 900 to not interfere other applications.
- Ensure that you have a network card that can be used as `Bridged` network or for more advanced setup check the `Configure NAT Network` from the menu.
- Ensure that `/var/lib` directory is writable and so `/etc/rc.conf` (file), `/usr/local/etc/rc.d` (directory), they are normally are.

#### 1) Log in to your FreeBSD Box and bring-up a root shell
    root@vmhost:~ #

#### 2) Download the latest release
  `fetch https://bhyve.npulse.net/release.tgz` or download manually from (this) github page

#### 3) Extract the archive you have downloaded
  `tar -xzvf release.tgz`
````
  x bhyve-webadmin/
  x bhyve-webadmin/utils/
  x bhyve-webadmin/API/
  x bhyve-webadmin/install.sh
  x bhyve-webadmin/Frontend/
  x bhyve-webadmin/.git/
  x bhyve-webadmin/Backend/
  x bhyve-webadmin/update.sh
  x bhyve-webadmin/Helper/
  x bhyve-webadmin/Helper/vmctl
  ...
````

#### 4) Enter `bhyve-webadmin` and run `install.sh`
  root@vmhost:~# `cd bhyve-webadmin && ./install.sh`

````
  Installing BVCP into your FreeBSD Installation within seconds ...

  Press [CTRL] + [C] to Abort !
  ...

          ██████╗ ██╗   ██╗ ██████╗██████╗
          ██╔══██╗██║   ██║██╔════╝██╔══██╗
          ██████╔╝██║   ██║██║     ██████╔╝
          ██╔══██╗╚██╗ ██╔╝██║     ██╔═══╝
          ██████╔╝ ╚████╔╝ ╚██████╗██║
          ╚═════╝   ╚═══╝   ╚═════╝╚═╝

  Bhyve Virtual-Machine Control Panel under FreeBSD
  [N] 2021-06-26 22:16:56 | application/vmserver/main.c | Initialising bhyve VM Server Application

  (+) The Software is producing pseudo filesystem scheme for virtual machines using symlinks
  Where to create metadata, iso_images, database, config, logs: (Does not need much space), default: [/vms]_>
````
#### 5) Enter a path (default: /vms) where some misc data placed, but not for storing virtual disks!!
````
    ...
        (!) Admin Credentials recreated,
        - User: admin
        - Password: AXN3jtPt
            
    [N] 2021-06-26 22:28:00 | SW | Program exited gracefully...
    Installation Finished!
    Navigate: https://[your-ip]:8086
````

#### 6) Installation is should be done, please write down your initial credentials and try access at `https://[your-ip]:8086`
#### 7) Please log-in and manage your account settings, before creation of any VM, please ensure you have added Storage and Network for the VM.


## API Interface
### General
````

  ██████╗ ██╗   ██╗ ██████╗██████╗
  ██╔══██╗██║   ██║██╔════╝██╔══██╗
  ██████╔╝██║   ██║██║     ██████╔╝
  ██╔══██╗╚██╗ ██╔╝██║     ██╔═══╝
  ██████╔╝ ╚████╔╝ ╚██████╗██║
  ╚═════╝   ╚═══╝   ╚═════╝╚═╝

Bhyve Virtual-Machine Control Panel under FreeBSD

[N] 2021-06-28 03:04:17 | application/vmserver/main.c | Initialising bhyve VM Server Application
Error: / ERR / invalid_parameter
Available Commands:
+ vm      | Virtual Machine Management
+ storage | Storage Management
+ switch  | Virtual Switch Management
+ user    | Built-in User Management
+ vminfo  | SysInfo
+ config  | Internal Storage

more details:  vm
cmd example:  vm list ALL
Note: type exit to Quit

_>
````

### VM Submenu
````
  _> vm
  * [vm] Available Commands:
    [Start/Stop Commands]
    + start [prefix]                                                          | Start virtual machine
    + check [prefix]                                                          | Check virtual machine
    + shutdown [prefix]                                                       | ACPI Shutdown
    + user [user]                                                             | Add user to the VM
    + log [prefix] [max_entries]                                              | Fetch VM Journal
    + kill [prefix]                                                           | Kill virtual machine
    + stop [prefix]                                                           | Stop virtual machine
    + list {prefix}                                                           | List virtual machine
    + destroy {prefix}                                                        | Destroy virtual machine
    + restart [prefix]                                                        | Restart virtual machine
 
    [Management Commands]
    + create [prefix] [description]                                           | New virtual machine
    + desc [prefix] [new_description]                                         | Modify virtual machine
    + note [prefix] {new_note}                                                | Add/Get note
    + clear [prefix]                                                          | Clear config (debug purpose)
    + set [prefix] [key] [value]                                              | Set core variables
      - keys: cpu.socket, cpu.core, memory, sys[linux,win,bsd] arch[intel,amd]
      - keys: auto.boot, vnc.wait
    - destroy [prefix]                                                        | Destroy virtual machine
    + list                                                                    | List virtual machine
 
    [Disk Management Commands]
    + disk create [prefix] [storage] [name] [size]                            | Create new disk
    + disk attach [prefix] [file] [desc] [slot] [ahci/virtio]                 | Attach Disk into VM
    + disk detach [prefix] [file]                                             | Detach Disk from VM
    + disk destroy [prefix] [ID/file]                                         | Delete Disk
    + disk resize [prefix] [file] [new_size]                                  | Resize Disk
    + disk list [prefix]                                                      | List Disks
 
    [CDROM Commands]
    + cdrom attach [prefix] [iso_file]                                        | Attach ISO file as CD-ROM
    + cdrom detach [prefix] [iso_file]                                        | Detach ISO
    + cdrom list [prefix]                                                     | List ISO Images
 
    [Network Commands]
    + nic add_virtio [prefix] [switch] {mac_addr}                             | Add VirtIO/NIC bound to switch
    + nic add_legacy [prefix] [switch] {mac_addr}                             | Add Intel/NIC bound to switch
    + nic change [prefix] [NIC] [switch]                                      | Change Switch
    + nic enable [prefix] [NIC]                                               | Enable NIC
    + nic disable [prefix] [NIC]                                              | Disable NIC
    + nic remove [prefix] [NIC]                                               | Remove NIC
    + nic list [prefix]                                                       | List Interfaces
````

### Storage Submenu
````
  _> storage
  * [storage] Available Commands:
  [Basic Commands]
  + list {active}                               | List Storages
  + create [mountpoint] [description]           | Enable new storage
  + modify [mountpoint] [desc] {enable/disable} | Modify existing storage
  + destroy [mountpoint]                        | Destroy Storage
````

### Switch Submenu
````
  _> switch
  * [switch] Available Commands:
  [Basic Commands]
  + create [prefix] [description]                                                       | Create new vSwitch
  + destroy [prefix]                                                                    | Destroy vSwitch
  + desc [prefix] [description]                                                         | Rename vSwitch
  + reload                                                                              | Reload Configuration
  + cleanup                                                                             | Clear OS configuration
  + bound [prefix] [iface]                                                              | Bound to network interface
  + unbound [prefix]                                                                    | UnBound from network interface
  + list                                                                                | List vSwitch
  + listDevs                                                                            | List Network Cards
````

### Switch Submenu
````
  _> user
  * [user] Available Commands:
  + ipinfo [ip_addr]                                                  | Show IP Geo Info
  + fetch [userID/mail/ALL]                                           | Get list of user(s)
  + logauth [mail] [TYPE] [IP] [CUID] [EXT]                           | Log Authentication
  + authlog [mail/ALL]                                                | Retrieve Authentication Logs
  + change [mail/ID] [new_name] [new_mail] [new_password (optional)]  | Modify User Settings
  + role [mail/ID] [USER/ADMIN]                                       | Modify User Role
  + create [name] [mail] [passwd]                                     | Create new user account
  + delete [mail/userID]                                              | Dele
````

### Config Submenu
````
 _> config
    * [config] Available Commands:
    [Basic Commands]
    + set [key] [value]   | Set config variable
    + get [key]           | Get config variable
    + del [key]           | Delete config variable
````
