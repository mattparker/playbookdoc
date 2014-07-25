# A Documentation tool for ansible playbooks

While ansible roles are written in python and are documented using ansible-doc, playbooks themselves are just
.yml and are supposed to be readable enough to document themselves.

'Documents itself' is, as far as I'm aware, not considered a viable long-term practice.  That is, it might
do at the time, but won't when you come back to it some time later.

This rather simple php tool scans all the .yml files in a directory and looks for 'docblocks' in them, and then
writes that out to some simple html files.

This is a very first version...

## Usage

To get it you'll need to clone this repo somewhere.  You'll also need php 5.4+ on your system, but there are no
other dependencies (unless you want to run the unit tests, you'll need phpunit).  I know python would
be more appropriate but I've not got the time right now.

Then, from within the directory where your playbooks and stuff are, create a docs/ directory, and then you can run

```bash

$ /path/to/playbookdocrepo/playbookdoc.php
```

and it'll put a bunch of html files there.  You can specify input directory using the `-i` option and the output
directory with the `-o` option.

However, you'll need some docblocks in your playbooks (and anything else that's a yaml file, at any depth), else
the output won't be very exciting.


## Docblocks

You'll need to write docblocks that look like this for them to be picked up:

```yml
---

###
#
# This is the short description of the overall file
#
# You can write a longer, multi-line description
# here too.
#
# @author Matt Parker
# @copyright Lamplight Database Systems Limited
# @licence BSD
# @random Tags are allowed
# @random And can feature multiple times
#
###


# (now some playbook stuff)


###
#
# You can add as many as you like in one file.
#
#
#
# Are not treated specially, except for the @role
# tag, which will look for a role named 'in_this_directory'
# in the roles/ directory of the current one, and link
# to the documentation for roles/in_this_directory/tasks/main.yml
# if it exists, or any other file in that role that it finds.
#
# Unfortunately, the ansible galaxy site doesn't appear to provide
# predictable urls (i.e. you can't do https://galaxy.ansible.com/roles/named_role)
# so roles not found in the current firectory don't get links.  It'd be nice
# if they could though...
#
# @role in_this_directory
#
###

# (more playbook)

```

You can add docblocks to all kinds of .yaml files (i.e. vars, tasks, handlers etc).

As far as I know there's no existing yaml docblock convention or style, but if you know of one that's different to
this do let me know.


### More things

It'd be nice to actually parse the yml between docblocks in a file and look for includes, roles, tags etc
and automatically add @tags and links, so your docblocks would only need to be commentary about what's actually happening.

Obviously the styling etc is somewhat rudimentary and could probably benefit from a little attention at some point.

I'd also like to write an MarkdownOutput writer, with the main aim that docs in github would render nicely.


### License

BSD.


### Copyright

2014, Matt Parker, Lamplight Database Systems Limited


