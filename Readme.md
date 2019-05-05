Appointments based on commands from GMX Media-Center
====

**Version:** 2.0.0
**Authors:** Steffen Nitter
**Release:** 27 December 2017

## Description

The project's original purpose was mainly to learn how google drive can be used from the CLI. In the previous version, it was used to store the todos.yml-file, but in version 2, we address the gmx media-center instead.

## Setup

- Download and use composer to get dependencies.
- sudo apt-get install davfs2
- mkdir ~/Mediacenter
- Prepare for mounting the gmx media center by inserting something like this into /etc/fstab
    echo 'https://mediacenter.gmx.net /home/snitter/Mediacenter    davfs   defaults,user,uid=1000,gid=1000,_netdev,noauto  0       0' >> /etc/fstab

    Attentions: It seems that davfs get confused, if two secrets are there, therefore we put directly into /etc/davfs2/secrets something like
        https://mediacenter.gmx.net steffen_nitter@gmx.de SomePassword123
    but not in ~/.davfs2/secrets.

    Put something like this into /etc/rc.loal
        /bin/sleep 10 && /bin/su -c "/home/snitter/Projekte/APPointer/execute appoint --download" - snitter

    - The packages dzen2 and at should be installed, to make alarm times work.
    - Still not sure, wether we should use notify-send instead, but than we need this ppa:
        sudo add-apt-repository ppa:leolik/leolik 
        sudo apt-get update
        sudo apt-get upgrade
        sudo apt-get install libnotify-bin
        pkill notify-osd
      In this case, probably a key-mapping like the following is convenient:
        bindsym $mod+c exec "/home/snitter/Projekte/APPointer/bin/console appoint --hide-alarm-time"

    - Use some script (e.g. systemctl) to execute "/home/snitter/Projekte/APPointer/execute app --umount" when shutting down.

## Todos

* There is no wizard yet for creating appointments, mostly because I have no clue how this should look like.
* Some buttons on the dzen-message-boxes might be nice to tell, if the alarm boxes should be shown again later.
* Also, there is no way to edit appointments yet.
* Script e.g. called /etc/systemd/user/sni-login.service
    [Unit]
    Description=Boottime logger

    [Service]
    Type=oneshot
    ExecStart=/home/snitter/Projekte/APPointer/bin/console login --wait --login
    User=snitter

    [Install]
    WantedBy=multi-user.target
* systemctl --user enable sni-login.service
* If it does not work, create a hard link on /etc/systemd/system
* systemctl enable sni-login.service
