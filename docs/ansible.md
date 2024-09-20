# Ansible

## Installation

You should have the correct [Python version](https://github.com/Alta-Multimedia-Limited/alta-hk-legacy/blob/main/ansible/.python-version) installed.

```shell
cd ansible
pip install -r requirements.txt
ansible-galaxy install -r requirements.yml
```

## Generate `.env`

```shell
cd ansible
ansible-playbook -i environments/local/hosts.yml playbooks/phpunit.yml
```
