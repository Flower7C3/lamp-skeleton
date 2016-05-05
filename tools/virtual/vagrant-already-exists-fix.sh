#!/usr/bin/env bash

VBoxManage list vms | grep "LAMPSkeleton" | sed "s/\(.*\) {\(.*\)}/\2/" > ~/Documents/lamp/.vagrant/machines/default/virtualbox/id