# Varilink - Libraries - Ansible Playbooks

David Williamson @ Varilink Computing Ltd

------

A library of Ansible playbooks that is used as a Git submodule across multiple projects. The usual vehicle for reuse in Ansible is Ansible roles, however we also share Ansible playbooks across more than one project repository as follows:

1. We use our [Services - Ansible](https://github.com/varilink/services-ansible) repository to manage the core services across our server estate. This uses our main hosts inventory. We simulate that host inventory in the *now* and *to-be* test environments within our [Services - Docker](https://github.com/varilink/services-docker) repository.

>>Since playbooks are essentially a mapping between hosts and roles, the same playbooks are used in those two repositories, indeed this is fundamental to the value of testing in the [Services - Docker](https://github.com/varilink/services-docker) repository.

2. Many of the playbooks in this repository are used in multiple, website projects. Each of those website projects provides project specific Ansible variables via `group_vars/` and `host_vars` directories, which are used by the common, website project specific playbooks in this repository.

## Contents

| Playbook               | Type    |
| ---------------------- | ------- |
| `copy-certificate.yml` | project |

## Usage

Add this repository as a submodule of other repositories that will use it, with a path within those other projects of `playbooks/`. Then, link `group_vars/` and `host_vars/` directories from those other project within the root directory of this repository, i.e. within that `playbooks/` directory. You can the execute these playbooks from the root directory of any other repository that uses them via:

```sh
ansible-playbook ./playbooks/PLAYBOOK
```
Where *PLAYBOOK* is provided by this repository

### copy-certificate.yml

This playbook copies the LetsEncrypt certificate for a FQDN - for example, `www.varilink.co.uk` - from one host to another host. This is useful because sometimes, if I am moving a website from one host to another host the process that I follow is:

1. Setup the website on the new host.

2. Modify the Varilink Computing Ltd office DNS service to direct requests to the website to the new host for testing purposes, while external to our office the website still resolves to the hold host.

3. If all is well, update our external DNS service to resolve the website to the new host.

At stage two, I can't use LetsEncrypt to install a certificate on the new host because it will fail the `HTTP-01` challenge, so we workaround this by copying the existing certificate across.

When running this playbook, you must provide the `target_host` as via `--extra-vars`; for example:

```sh
ansible-playbook --extra-vars target_host=prod4 ./playbooks/copy-certificate.yml
```

The playbook will prompt for the `subdomain` to act upon. Alternatively you can also provide this via `--extra-vars`; for example:

```sh
ansible-playbook --extra-vars target_host=prod4 --extra-vars subdomain=www ./playbooks/copy-certificate.yml
```

The `domain_name` should be configured within the project's Ansible variable files. The playbook will look for the certificate to copy on every host within the `wordpress` inventory group with the exception of the `target_host`. You can of course limit the playbook's actions to only the required hosts, which are the `target_host`, the host that you know the certificate already exists on and the `localhost`; for example:

```sh
ansible-playbook --extra-vars target_host=prod4 --extra-vars subdomain=www --limit=prod3,prod4,localhost ./playbooks/copy-certificate.yml
```