# Backend-IP-Login

Remember the login based on the network mask or ip. 

Only for development, unsafe for live environments!

# Warning (Please read!)
Never ever use this extension in a production system. If a company has the same IP address for external connections
(which is not uncommon) and one of them logged in as administrator everyone from the same company can select every user(if display account 
list is enabled) or get auto logged in as administrator(if display account list is disabled).

This extension purely got created thanks to my laziness to check every access for every development server
in my old company(which managed logins with .txt files....).