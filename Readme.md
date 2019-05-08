Appointments using a remote database for syncing clients
====

**Version:** 2.0.0
**Authors:** Steffen Nitter
**Release:** 27 December 2017

## Description

The project's original purpose was mainly to learn how google drive can be used from the CLI. In the previous version, it was used to store the todos.yml-file, but in version 2, we address the gmx media-center instead.

## Setup

* Download and use composer to get dependencies.
* exec --no-startup-id i3-msg 'workspace1; exec urxvt -e bash -c "/home/snitter/Projekte/APPointer/bin/console appoint -s && bash"'
* copy env.dist to .env and adjust
* If using i3, we recommend exec --no-startup-id i3-msg 'workspace1; exec urxvt -e bash -c "/path/to/APPointer/bin/console appoint -s && bash"'

## Todos

We use notify-send, so alarm times should be shown on every decent system. To make them stay (for 20 minutes) we recommend
    `sudo add-apt-repository ppa:leolik/leolik`
Together with i3, we recommend
    `bindsym $mod+c exec "/home/snitter/Projekte/APPointer/bin/console appoint --hide-alarm-time`
