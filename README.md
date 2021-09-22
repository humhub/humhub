# Install WIP



1.  https://docs.humhub.org/docs/develop/environment
	1.  create database  	
	1.  	git clone --recurse-submodules https://github.com/StruxureIO/TheBlackSheepHub.git
	1.  	composer install
	2.  Run web installer

1.  Install Calendar and External Calendar Modules through admin panel:  "yourhumhubdir"/index.php?r=marketplace%2Fbrowse


# Update Module
1.  Update module

		cd "module_directory"
 		git checkout master //needed for the first commit
        git add 
        git commit
        git push
1.  Update Parent  
  		
        cd "parent_repo_direcotry" 
	  	git add 
        git commit
        git push

# Update on Server

    ssh into server
    
    cd TheBlackSheepHub

    git pull --recurse-submodules


HumHub - Social Network Kit
===========================

[![Test Status](https://github.com/humhub/humhub/workflows/PHP%20Codeception%20Tests/badge.svg)](https://github.com/humhub/humhub/actions)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)
[![CLA assistant](https://cla-assistant.io/readme/badge/humhub/humhub)](https://cla-assistant.io/humhub/humhub)

HumHub is a feature rich and highly flexible OpenSource Social Network Kit written in PHP.

It's perfect for individual:
- Social Intranets
- Enterprise Social Networks
- Private Social Networks

More information:
- [Homepage & Demo](http://www.humhub.org)
- [Documentation & Class Reference](http://docs.humhub.org)
- [Community](http://community.humhub.com/)
- [Licence](https://www.humhub.com/licences)

