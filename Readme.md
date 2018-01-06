TODOs on Gogle-Drive
====

**Version:** 1.0.0
**Authors:** Steffen Nitter
**Release:** 27 December 2017

## Description

The project's purpose is mainly to learn how google drive can be used from the CLI.

## Setup

Download and use composer to get dependencies.
It is recommended to add the following to the i3-config-file to show the todos on startup only:
`exec --no-startup-id i3-msg 'workspace1; exec urxvt -e bash -c "/some/path/GoogleClient/execute todo -s && bash"'`

To see other arguments for the todo-command use
`/some/path/GoogleClient/execute todo --help`