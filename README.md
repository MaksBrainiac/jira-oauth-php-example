# Atlassian PHP OAuth Example
This is an OAuthWrapper update for connecting with Atlassian Jira's OAuth service, thus allowing you to use their REST api.
This example uses guzzlehttp/guzzle library instead of abandoned guzzle/guzzle. 

Please check https://bitbucket.org/atlassianlabs/atlassian-oauth-examples/src/master/php/

You will need to generate a private/public key and setup an Application Link inside of Jira.  You can generate the private/public key by running the following from your command line:

	openssl genrsa -out jira_privatekey.pem 1024
    openssl req -newkey rsa:1024 -x509 -key jira_privatekey.pem -out jira_publickey.cer -days 365
    openssl pkcs8 -topk8 -nocrypt -in jira_privatekey.pem -out jira_privatekey.pcks8
    openssl x509 -pubkey -noout -in jira_publickey.cer  > jira_publickey.pem

Next you'll want to setup your application link inside of Jira, you can find instructions for that [here](https://confluence.atlassian.com/display/JIRA/Configuring+OAuth+Authentication+for+an+Application+Link).

*Note: you'll be dealing with the incoming authentication and the public key you generated above will need to be pasted into the OAuth window.*

