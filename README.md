# Varilink - Libraries - Ansible Playbooks

David Williamson @ Varilink Computing Ltd

------

A library of Ansible playbooks that is used as a Git submodule across multiple projects. The usual vehicle for reuse in Ansible is Ansible roles, however we also share Ansible playbooks across more than one project repository as follows:

1. We use our [Services - Ansible](https://github.com/varilink/services-ansible) repository to manage the core services across our server estate. This uses our main hosts inventory. We simulate that host inventory in the *now* and *to-be* test environments within our [Services - Docker](https://github.com/varilink/services-docker) repository.

    Since playbooks are essentially a mapping between hosts and roles, the same playbooks are used in those two repositories, indeed this is fundamental to the value of testing in the [Services - Docker](https://github.com/varilink/services-docker) repository.

2. Many of the playbooks in this repository are used in multiple, website projects. Each of those website projects provides project specific Ansible variables via `group_vars/` and `host_vars` directories, which are used by the common, website project specific playbooks in this repository.

## Contents

| Playbook               | Type    |
| ---------------------- | ------- |
| `copy-certificate.yml` | project |
| `copy-subdomain.yml`   | project |

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

### copy-subdomain.yml

This playbook makes a copy of a WordPress site to another subdomain for the same domain and on the same host; for example makes a copy of `preprod.varilink.co.uk` to `www.varilink.co.uk` on the same host. By *copy* we mean that the new WordPress site will be identical, except that the change in subdomain will be reflected in both its database and its `wp-config.php` file.

The example given above reflects a common usage scenario for this playbook; for example as follows:

1. `www.varilink.co.uk` is hosted on `prod3` and we want to move it to `prod4`.

2. We create `preprod.varilink.co.uk` on `prod4` and develop and test it until we are confident that it's ready to replace the existing `www.varilink.co.uk`.

3. We copy `preprod.varilink.co.uk` on `prod4` to `www.varilink.co.uk`, also on `prod4`, ahead of making the DNS change to make it the new, live `www.varilink.co.uk`.

To run this playbook; for example:

```sh
ansible-playbook --limit=prod4 ./playbooks/copy-subdomain.yml
```

Note that since this playbook works on one host only, you should use `--limit` as above to target the relevant, specific host.

The playbook will prompt for three variables:
- `current_subdomain`; for example `preprod`
- `to_be_subdomain`; for example `www`
- `to_be_port`; for example `8084`

The value for `to_be_port` should be a port that is not already used on the host, which can be determined by examination of `/etc/apache2/ports.conf` on that host.

Of course, these variables can also be set using `--extra-vars` on the command line.
