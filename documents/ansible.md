# Ansible

## Installation

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
