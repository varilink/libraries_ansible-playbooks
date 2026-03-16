# Libraries - Ansible Playbooks

David Williamson @ Varilink Computing Ltd

------

A library of Ansible playbooks that is reused as a Git submodule across multiple projects. The usual vehicle for reuse in Ansible is Ansible roles, however we also share Ansible playbooks across more than one project repository as follows:

1. We use our [Services - Ansible](https://github.com/varilink/services-ansible) repository to manage the core services across our server estate. This uses our main hosts inventory. That host inventory is simulated in the *now* and *to-be* test environments within our [Services - Docker](https://github.com/varilink/services-docker) repository.

2. Many of the playbooks in this repository are used in multiple, mainly WordPress, website projects. Each of those website projects provides project specific Ansible variables via `group_vars/` and `host_vars/` directories, which are then used by the common, website project specific playbooks in this repository to make them applicable to each of these projects.

## Services Topology

The [Services - Ansible](https://github.com/varilink/services-ansible) and [Services - Docker](https://github.com/varilink/services-docker) repositories both use the Ansible roles in the [Libraries - Ansible Roles](https://github.com/varilink/libraries_ansible-roles) and the Ansible playbooks provided by this repository. That is fundamental to the value of the [Services - Docker](https://github.com/varilink/services-docker) repository as it means that it can be used to test our Ansible playbooks and roles libraries ahead of running them targetting our live hosts.

In order to enable this the patterns used in the hosts lines to scope plays in the playbooks in this repository use a combination of:

1. Group names that can then be mapped to the environment specific hosts within each environment's hosts inventory.

| Group Name        | Used For That                             |
| ----------------- | ----------------------------------------- |
| backup_exceptions | Are not to be backed up.                  |
| dns               | Provide a DNS service.                    |
| external          | Are external to our office network.       |
| internal          | Are within our office network.            |
| wordpress         | Provide a WordPress site hosting service. |

2. Aliases for those single Ansible hosts that fulfil a specific function within our server estate.

| Alias   | Used For                                                                                      |
| ------- | --------------------------------------------------------------------------------------------- |
| gateway | Host that provides Internet gateway services at the boundary of our internal, office network. |
| hub     | Main office based host that provides all our internal, office network business services.      |
| mail    | Our external, Internet hosted, email gateway host.                                            |

## Contents

This primary contents of this repository are the playbooks in its root folder. As described above, playbooks are either used to manage the Varilink core services (Type=*serivces*) or within WordPress website projects (Type=*project*). The implication of this distinction is that whereas playbooks of type *services* are run from the [Services - Ansible](https://github.com/varilink/services_ansible) repository, playbooks of type *project* are run from one of a number of project repositories.

| Playbook                                                                               | Type     |
| -------------------------------------------------------------------------------------- | -------- |
| [`bootstrap-server.yml`](#bootstrap-serveryml)                                         | services |
| [`configure-domain-mail.yml`](#configure-domain-mailyml)                               | project  |
| [`copy-certificate.yml`](#copy-certificateyml)                                         | project  |
| [`install-services-portal.yml`](#install-services-portalyml)                           | services |
| [`install-services.yml`](#install-servicesyml)                                         | services |
| [`link-backups-to-dropbox.yml`](#link-backups-to-dropboxyml)                           | services |
| [`stop-services.yml`](#stop-servicesyml)                                               | services |
| [`wp-cli-run-command.yml`](#wp-cli-run-commandyml)                                     | project  |
| [`wp-cli-run-script.yml`](#wp-cli-run-scriptyml)                                       | project  |
| [`wp-copy-site.yml`](#wp-copy-siteyml)                                                 | project  |
| [`wp-create-site.yml`](#wp-create-siteyml)                                             | project  |
| [`wp-delete-site.yml`](#wp-delete-siteyml)                                             | project  |
| [`wp-put-site-into-maintenance-mode.yml`](#wp-put-site-into-maintenance-modeyml)       | project  |
| [`wp-restore-site.yml`](#wp-restore-siteyml)                                           | project  |
| [`wp-take-site-out-of-maintenance-mode.yml`](#wp-take-site-out-of-maintenance-modeyml) | project  |

This repository also contains `files/` and `templates/` folders that are used by one or more of the playbooks for files and templates that are **not** referenced from roles in [Libraries - Ansible Roles](https://github.com/varilink/libraries_ansible-roles).

## Usage

In order to use the playbooks provided by this repository within an Ansible project repository you must do the following:

1. Add this repository as a submodule of the Ansible project repository at the path `playbooks/`.

2. Add the [Libraries - Ansible Roles](https://github.com/varilink/libraries-ansible_roles) repository as a submodule of the Ansible project repository at the path `roles/`.

3. If you need to provide project specific Ansible variables, then add an `inventory/` directory to your Ansible project repository.

4. Within that `inventory/` directory, add a `hosts.yml` file with the following contents:

```yaml
all:
  children: {}
```

5. Create `inventory/group_vars/` and/or `inventory/host_vars/` directories and define your project specific variables within them.

6. With the project's root directory, create an `ansible.cfg` file with the following contents:

```conf
[defaults]
inventory = /etc/ansible,./inventory
roles_path = ./roles
```

Remember that steps 3, 4 and 5 as well as the `inventory = /etc/ansible,./inventory` line in the `ansible.cfg` file are only required if you need to create project specific Ansible variables. It's very common that you will need to do this but you can omit these aspects if you don't.

You can then execute these playbooks from the root directory of any other repository that uses them via:

```sh
ansible-playbook ./playbooks/PLAYBOOK
```
Where *PLAYBOOK* is provided by this repository and is one of the playbooks listed above. More detailed usage instructions follow for each individual playbook.

### bootstrap-server.yml

Bootstraps an office server, that is not a Cloud server nor based on a Raspberry Pi image.

### configure-domain-mail.yml

Configures the email service for a project domain.

### copy-certificate.yml

*** OUT OF DATE ***<br>
The usage instructions for this playbook are out of date, as is the function of the playbook itself.<br>
*** OUT OF DATE ***

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

### install-services-portal.yml

Installs a portal to securely expose information about office network hosted services externally.

### install-services.yml

As the name suggest, this is the playbook that installs the Varilink core services. In effect, it maps roles from our [Libraries - Ansible Roles](https://github.com/varilink/libraries_ansible-roles) to hosts and host groups in the inventory.

If you examine this playbook, it deploys roles as follows:

| Inventory Host or Group                          | Deployed Roles                                                                                                                                                     |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| gateway (host)                                   | dns_api<br>dynamic_dns<br>mail_certificates<br>monitor_gateway                                                                                                     |
| hub (host)                                       | backup_director<br>backup_storage<br>backup_web<br>calendar<br>database<br>dns<br>file_share<br>git_origin<br>mail_internal<br>monitor_dashboard<br>monitor_server |
| mail (host)                                      | mail_external                                                                                                                                                      |
| all (group)                                      | monitor_client                                                                                                                                                     |
| all (group)<br>not backup_exceptions(group)      | backup_client                                                                                                                                                      |
| all (group)<br>not mail (host)<br>not hub (host) | mta                                                                                                                                                                |
| internal (group)<br>not hub (host)               | dns_client                                                                                                                                                         |
| wordpress (group)                                | database<br>dns_api<br>reverse_proxy<br>wordpress_apache                                                                                                           |

Note that the roles identified in that table are those that are **explicitly** deployed by the playbook. Some roles import other roles, effectively deploying those imported roles too, but the playbook itself does not do that explicity.

### link-backups-to-dropbox.yml

Links a host to Dropbox for integration with backup services.

### stop-services.yml

Stops the base Varilink services; backup, DNS, monitoring, WordPress hosting, etc.

### wp-cli-run-command.yml

Run a WP-CLI command against a WordPress site.

### wp-cli-run-script

Runs a WP-CLI script against a WordPress site. The script can either be specific to the project or one of the shared scripts from the [Libraries - WP CLI Scripts](https://github.com/varilink/libraries-wp_cli_scripts) repository.

When you run this playbook, it will prompt for the name of the script to run if that hasn't been provided via `--extra-vars`; for example like this:

```sh
ansible-playbook --limit=gateway --extra-vars wp_cli_script=init ./playbooks/run-wp-cli-script.yml
```

The `.sh` extension will be added to the name to determine the name of script file to look for. In the example above the script file `init.sh` will be searched for; first in the directory `wordpress/scripts`, which must be the path for project scripts, and then in the directory `wordpress/varilink-scripts`, which must be the path for shared scripts. The `wordpress/varilink-scripts` directory will only be searched if the script is not found in the `wordpress/scripts` directory.

### wp-copy-site.yml

*** OUT OF DATE ***<br>
The usage instructions for this playbook are out of date, as is the function of the playbook itself.<br>
*** OUT OF DATE ***

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

### wp-create-site.yml

Creates a WordPress site.

### wp-delete-site.yml

Deletes a WordPress site.

### wp-put-into-maintenance-mode.yml

Puts a WordPress site into maintenance mode.

### wp-restore-site.yml

Restores a WordPress site from backup.

### wp-take-wordpress-site-out-of-maintenance-mode.yml

Takes a WordPress site out of maintenance mode.
