mojo-site_ex
============

Version: 0.6
Author: [Jordan Mack](http://jmack.parhelic.com)

site_ex is an extension to the default MojoMotor site tag, which adds additional functionality.


##Installation

Copy the 'site\_ex' folder into system/mojomotor/third_party.

One you copy in the site\_ex folder, site\_ex.php should be locared in the following location:

    mojomotor
      third_party
        site_ex
          libraries
            site_ex.php


##Usage

site\_ex is designed as a drop-in replacement for MojoMotor's site tag. site\_ex is an extension of site, and includes additional functionality. All the default functionality of site can be used with site\_ex. Please see the [MojoMotor documentation](http://mojomotor.com/user_guide/mojo_tags.html) for normal usage of site.



Example:

    {mojo:site:page_list}

becomes

    {mojo:site_ex:page_list}


Please see the [MojoMotor documentation](http://mojomotor.com/user_guide/mojo_tags.html#page_list) for normal usage of page_list.

The {mojo:site_ex:page\_list} tag can have the following **additional** parameters specified:

**li\_id\_prefix** - Specify the prefix for the ID attribute of the li tags. (Default:"mojo\_page\_list\_")

    {mojo:site_ex:page_list li_id_prefix="mojo_nav_"}

**omit\_li\_ids** - If set to "true" the ID attribute of the li tags will be omitted completely. (Default: false)

    {mojo:site_ex:page_list omit_li_ids="true"}
