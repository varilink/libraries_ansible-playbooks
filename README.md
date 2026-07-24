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
| [`wp-copy-certificate.yml`](#wp-copy-certificateyml)                                   | project  |
| [`wp-copy-site.yml`](#wp-copy-siteyml)                                                 | project  |
| [`wp-create-site.yml`](#wp-create-siteyml)                                             | project  |
| [`wp-delete-site.yml`](#wp-delete-siteyml)                                             | project  |
| [`wp-deploy-dev-to-host.yml`](#wp-deploy-dev-to-hostyml)                               | project  |
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

### wp-copy-certificate.yml

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

### wp-copy-site.yml

This playbook takes a copy of one instance of the website for a domain (the copy "from instance") and restores it as another instance of the website for the same domain (the copy "to instance"). The to instance can be on a different host to the host for the from instance and/or for a different subdomain to the subdomain for the from instance.

This playbook will prompt for the `from_host` and the `to_host` and will only act on those hosts, so there's no need to use the `--limit` option to the `ansible-playbook` command when running this playbook. Of course these variables can also be set on the command line, in which case the playbook won't prompt for them; for example:

```sh
ansible-playbook --extra-vars from_host=prod4 --extra-vars to_host=prod5 ./playbooks/wp-copy-site.yml
```

If either of the hosts is configured for only a single subdomain, then that subdomain will be automatically selected as the from subdomain on the from host or to subdomain on the to host. If either of the hosts is configured for multiple subdomains then the playbook will prompt the user to select the subdomain to be used.

In essence, the playbook consists of two steps. The first step generates two files in the `/tmp` directory on the controller:

1. A compressed tar archive of the copy from website's WordPress files, with the name `[FQDN]-[YYYY-MM-DD]-[RANDOM_HEX_STRING].tgz`. The generated *random hex string* will be 7 characters long.

2. A compressed SQL file containing a dump of the copy from website's database, with the name `[DATABASE]-[YYYY-MM-DD]-[RANDOM_HEX_STRING].sql.gz`. For WordPress sites, we generate the database name from the FQDN, with the `.` separators replaced by underscore characters.

When these files are generated in the same playbook execution, they will share in their file names the same date, which is the date of they are generated, and the same randomly generated hex string.

The second step uses these two files to create or update the copy to website based on their contents. If you ever want to rerun the second step without generating new export files from the copy from website but reusing export files that remain on the controller from a previous run, you can do so using command line vars, in the way illustrated in this example:

```sh
ansible-playbook --extra-vars from_host=prod4 --extra-vars to_host=prod5 --extra-vars dump_date_string=2026-06-05 --extra-vars dump_hex_string=08c83bf ./playbooks/wp-copy-site.yml
```

#### Migration process

This playbook is central to the process for migrating WordPress websites from one host to another host, which is often performed as part of the upgrade of our WordPress hosting environment. A key feature of migration is that the from and to subdomains for the copy are the same. The steps to do this are as follows:

1. Configure a WordPress site for the `to_host` of the copy operation using `inventory/host_vars` in the project's Ansible repository.

2. Run this playbook to copy the WordPress site from the `to_host` to the `from_host`.

    In a migration copy, the from and to subdomains for the WordPress site are the same. This causes this playbook to do two things that it doesn't do when those subdomains are different:

    i. Configure a DNS record in the internal DNS mask service for the WordPress site, even if it is externally hosted.

    ii. Generate a self-signed SSL certificate rather than obtain one from LetsEncrypt.

    These actions are of course to allow for testing of the copied WordPress website on its new host prior to updating the relevant external DNS zone to point to that new host.

3. Use a web browser to test the WordPress website on its new host accordingly.

    Personally, I use the Chrome web browser and this means that I have to do the following in order to do this.

    i. Go to `chrome://settings/security` in Chrome and disable the `Use secure DNS` option so that Chrome uses our DNS mask service for lookups. Use this in combination with `chrome://net-internals/#dns` to clear host resolver cache and do check lookups.

    ii. When visiting the WordPress website on its new host, accept the security exception raised because the SSL certificate is self-signed and so can't be verified. Use the browser in Incognito mode so that your acceptance of this security exception is not remembered after you close the Incognito mode browser window.

---

You may pause at this point in the migration steps for as long as you like. This allows you to take as long as you like testing the website that has been created by the copy. It also means that you can execute the subsequent steps 4 to 8 by themselves within a subsequent implementation window; for example outside of office hours.

---

4. When you have finished testing the WordPress website on its new host, put the current live instance on the host we're migrating from into maintenance mode using the `wp-put-site-into-maintenance-mode.yml` playbook to prevent any further updates to it.

    This also serves to be certain:
    
    i. When users are accessing the WordPress website on its new host, since then they will no longer see the maintenance mode page.

    ii. When the DNS change made in step 7 below has propagated, at least as far as Germany. The HTTP 503 Service Unavailable response returned by the WordPress website in maintenance mode will result in us receiving a "service down" report from Uptime Robot's monitoring servers in Germany. After step 7 has been executed and the resulting DNS changes have propagated as far as their monitoring servers, they will report that the service is back up again, since they will then be monitoring the WordPress website on its new host.

5. Run this playbook again to repeat the WorPress website copy. This serves to ensure that no changes that have been made since you ran the WordPress website copy in step 2 are not lost.

6. Run the `wp-copy-certificate.yml` playbook to replace the self-signed certificate that was created in step 2 with the LetsEncrypt certificate for the copied WordPress website taken from the host it was copied from. Note that does not yet configure `certbot` to be able to renew certificates for the WordPress website in its new location, see step 8. To check the details of the certificate that the website is now using use this command on the desktop:

```sh
openssl s_client -connect your-domain:443 -servername your-domain </dev/null 2>/dev/null |
openssl x509 -noout -subject -issuer -enddate
```

    You **could** do this at step 2 and not have to deal with an intermediate, self-signed certificate at all but as I said earlier, I personally like the fact that while I am working with a self-signed certificate it reminds me that I haven't yet haven't yet switched over the DNS record.

7. **Immediately** after having run step 6, run the `wp-create-site.yml` playbook with the `--tag dns` option for the WordPress website. This will do two things:

    i. Remove the temporary DNS mask from the office DNS server that was put in place to enable testing of the copy to WordPress website while the copy from WordPress website was still live.

    ii. Update the DNS records for the WordPress website in the project's external DNZ zone to put into effect the switch over from copy from to copy to hosts.

    I emphasise immediately above because once I've installed a LetsEncrypt certificate for the copied WordPress site, I am no longer reminded that I haven't yet switched over the DNS record by the browser security alert raised by the use of a self-signed certificate.

8. On receipt of service up notifications from Uptime Robot, which indicates that the DNS changes in the previous step have propagated (see step 4), run the `wp-create-site.yml` playbook again, this time with the `--tag proxy` option for the copy to host for the WordPress website. This will trigger `certbot` to create a certificate for the WordPress website there and in doing so also configure `certbot` to be able to renew the certificate when it comes up to expiry. You can use the command given in step 6 before and after this step to check the certificate details have been updated.

9. Run the `wp-delete-site.yml` playbook for the WordPress website on its old host and remove the configuration for it there in `inventory/host_vars/` in the project's Ansible repository to tidy up.

#### Combined Migration and Redesign Scenario

A recent implementation for one of my projects created a special scenario in which the migration of a website from one host to another host was combined with the implementation of a redesign. Here wsa the topology of website instances before and after the implementation.

Before:
- www.example.com on old host
- redesign.example.com on new host

After:
- www.example.com on new host as a copy of redesign.example.com

In order to achieve this it was necessary to do it in two steps as follows:
1. Migrate www.example.com from the old host to the new host.
2. Copy redesign.example.com to www.example.com on the new host.

### wp-create-site.yml

Creates a WordPress site.

### wp-delete-site.yml

Deletes a WordPress site.

### wp-deploy-dev-to-host.yml

Takes the backup of a WordPress website that been developed using a project's Docker Compose repository and deploys it to a host.

### wp-deploy-dev-to-host.yml

This playbook uses a backup of a WordPress site that's under development on the user desktop using a project's Docker Compose repository and deploys it to a website instance on a host.

### wp-put-into-maintenance-mode.yml

Puts a WordPress site into maintenance mode. It will prompt for the name of the host of the WordPress website and also that website's subdomain if there are more than one configured for the host.

The playbook generates a random, complex token for bypassing the maintenance mode and reports its value. If you manually add a session cookie with the name `maintenance_bypass` and set it to that reported value, then you will be able to bypass maintenance mode to access the underlying website. Others who do not know this token's value will not be able to do that of course.

Note that a WordPress site in maintenance mode as implemented by this playbook will give a HTTP 503 Service Unavailable response and so health monitors that take a HTTP 200 OK as an indicator of service health with raise alerts.

### wp-restore-site.yml

This playbook restores a WordPress site from a backup taken by our Bacula based backup services. Prior to running this playbook, it's necessary to do a Bacula restore. To do this run the `bconsole` command. At the `bconsole` command prompt, run `restore`.

I find that the best approach is to then select option 5, *Select the most recent backup for a client* from the list of choices to select JobIds. Select the client that the backup was taken from and after `bconsole` has built the directory tree for the JobIds that collectively comprise the most recent backup for that client, mark the files to be restored.

The files that you need to mark are the database SQL dump, which will be in the `/tmp` folder in the directory tree and the top-level folder for the website's WordPress files, which will be at `/var/www/[wp_server_name]/` in the directory tree.

Note that the playbook relies on the WordPress site to be restored being configured in `inventory/host_vars/`.

### wp-take-wordpress-site-out-of-maintenance-mode.yml

Takes a WordPress site out of maintenance mode.
