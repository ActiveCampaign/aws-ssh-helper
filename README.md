AWS SSH Helper
==============

This tool makes SSH configuration from AWS EC2 metadata. It depends on
[assh](https://github.com/moul/advanced-ssh-config).

Requirements
------------

* PHP 7.1+

Installation
------------

```sh
$ curl -sO https://activecampaign.github.io/aws-ssh-helper/downloads/aws-ssh-helper.phar
$ chmod +x aws-ssh-helper.phar
```

Usage
-----

If you have resources in two regions -- us-east1 and us-west-1 -- and
two accounts/profiles, you'd run:

```bash
aws-ssh-helper.phar --profile=production --region=us-east-1               > ~/.ssh/assh.d/bangpound-production-us-east-1.yaml
aws-ssh-helper.phar --profile=production --region=us-west-1               > ~/.ssh/assh.d/bangpound-production-us-west-1.yaml
aws-ssh-helper.phar --profile=staging    --region=us-east-1 --prefix=stg- > ~/.ssh/assh.d/bangpound-staging-us-east-1.yaml
aws-ssh-helper.phar --profile=staging    --region=us-west-1 --prefix=stg- > ~/.ssh/assh.d/bangpound-staging-us-west-1.yaml
```

* Instance ID is used as the unique hostname.
* Name tag, public IP, public hostname, private IP and private
  hostname are aliases for each host. If there is duplication of aliases
  for multiple host configurations, the behavior when using that alias
  is not defined.
* For instances in a private subnet, an instance in the same VPC with
  `bastion` in its name will be used as a proxy.
