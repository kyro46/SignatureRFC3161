# ILIAS 4.4 Signature Plugin #

### Digital signature and timestamp for exams in ILIAS 4.4 ###

This plugin will add a digital signature to the PDF generated after exams, if the corresponding flag in the testoptions is set.
It uses the free DFN-timestamp-service with the RFC-3161 standard („Internet X.509 Public Key Infrastructure Time-Stamp Protocol (TSP)“).

### Usage ###

Install the plugin

```bash
mkdir -p Customizing/global/plugins/Modules/Test/Signature  
cd Customizing/global/plugins/Modules/Test/Signature
git clone https://github.com/kyro46/SignatureRFC3161.git
```

and activate it in the ILIAS-Admin-GUI.

Upload a certificate. For testing create your own certificate e.g. with the Java Keytool:
* keytool -genkey -keyalg RSA -alias selfsigned -keystore key.keystore -storepass password -validity 360 -keysize 2048

Before the test, activate:
* Enable Archiving
* Digitally sign test submissions

After the test create the export "Test Archive File". The signed PDFs will be stored inside.

### The signPdf.jar ###

The Repo contains
* the main files for the signPdf.jar at /SignPdf
* the ready to use singPdf.jar in /resources


### Credits ###
* Original Javacode by Steffen Dienst, University Leipzig for ElateXam, 2010
* Porting to a plugin for ILIAS 4.4 by Yves Annanias, University Halle, 2014
* Further development by Christoph Jobst, University Halle, 2014/2015/2016