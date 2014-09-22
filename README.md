# ILIAS 4.4 Signature Plugin #

### Digital signature and timestamp for exams in ILIAS 4.4 ###

This plugin will add a digital signature to the PDF generated after exams, if the corresponding flag in the testoptions is set.
It uses the free DFN-timestamp-service and the RFC-3161 standard („Internet X.509 Public Key Infrastructure Time-Stamp Protocol (TSP)“).

### Usage ###

Install the plugin to
* Customizing/global/plugins/Modules/Test/Signature

and activate it in the ILIAS-Admin-GUI.

Upload a certificate. For testing create your own certificate e.g. with the Java Keytool:
* keytool -genkey -keyalg RSA -alias selfsigned -keystore key.keystore -storepass password -validity 360 -keysize 2048


### Build ###

The Repo contains
* the main files for the signPDF.jar at /SignPdf
* the general plugincode for ILIAS as /signatureRFC3161

The .jar has to be stored in /plugin/resources for the plugin to work.


### Credits ###
* Original Javacode by Steffen Dienst, University Leipzig for ElateXam 2010
* Porting to a plugin for ILIAS 4.4 by Yves Annanias, University Halle 2014