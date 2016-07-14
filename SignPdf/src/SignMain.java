package de.sign;

/*

Copyright (C) 2016 Christoph Jobst

This program is free software, you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY, without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program, if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.net.InetAddress;
import java.net.UnknownHostException;
import java.security.GeneralSecurityException;
import java.security.KeyStore;
import java.security.KeyStoreException;
import java.security.NoSuchAlgorithmException;
import java.security.PrivateKey;
import java.security.Security;
import java.security.UnrecoverableKeyException;
import java.security.cert.Certificate;
import java.security.cert.CertificateException;

//Bouncycastle bcpkix-jdk15on-154
//Bouncycastle bcprov-jdk15on-154
import org.bouncycastle.jce.provider.BouncyCastleProvider;

//itextpdf-5.5.9
import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.pdf.PdfReader;
import com.itextpdf.text.pdf.PdfSignatureAppearance;
import com.itextpdf.text.pdf.PdfStamper;
import com.itextpdf.text.pdf.security.BouncyCastleDigest;
import com.itextpdf.text.pdf.security.ExternalDigest;
import com.itextpdf.text.pdf.security.ExternalSignature;
import com.itextpdf.text.pdf.security.MakeSignature;
import com.itextpdf.text.pdf.security.MakeSignature.CryptoStandard;
import com.itextpdf.text.pdf.security.PrivateKeySignature;
import com.itextpdf.text.pdf.security.TSAClient;
import com.itextpdf.text.pdf.security.TSAClientBouncyCastle;


public class SignMain {

	private String orgFile;
	private String key_alias;
	private String certificate_chain_alias;
	private String keystore_password;
	private String private_key_password;
	private String keystore_file;
	private String tsa_URL;
	
	public SignMain(String[] args) {
		try {
			this.orgFile = args[0];
			this.key_alias = args[1]; 	
			this.certificate_chain_alias = args[2]; 	
			this.keystore_password = args[3]; 	
			this.private_key_password = args[4]; 
			this.keystore_file = args[5];
			this.tsa_URL = args[6];

		} catch (Exception e){
			e.printStackTrace();
		}
	}
	
	public static void main(String[] args) throws DocumentException, IOException, GeneralSecurityException {
		SignMain signM = new SignMain(args);
		signM.sign();
	}
	
	public void sign() throws DocumentException, IOException, GeneralSecurityException {
		
		PdfReader reader = new PdfReader(this.orgFile);
	    OutputStream os = new FileOutputStream(this.orgFile.replace(".pdf", "SIGN.pdf"));
	    PdfStamper stamper = PdfStamper.createSignature(reader, os, '\0');

	    // Create appearance
	    PdfSignatureAppearance appearance = stamper.getSignatureAppearance();
	    Rectangle cropBox = reader.getCropBox(1);
	    float width = 50;
	    float height = 50;
	    Rectangle rectangle = new Rectangle(cropBox.getRight(width)-20, cropBox.getTop(height)-20,cropBox.getRight()-20, cropBox.getTop()-20);
	    appearance.setVisibleSignature(rectangle, 1, "sig");
	    appearance.setLocation(getHostname());
	    appearance.setReason("Evidence of document integrity");
	    appearance.setCertificationLevel(1); // 1 = CERTIFIED_NO_CHANGES_ALLOWED
	    appearance.setAcro6Layers(false);
	    appearance.setLayer2Text("");

	    //Sign
	    Security.addProvider(new BouncyCastleProvider());
	    TSAClient tsc = new TSAClientBouncyCastle(this.tsa_URL);
	    ExternalDigest digest = new BouncyCastleDigest();
	    ExternalSignature signature = new PrivateKeySignature(getPrivateKey(), "SHA-1", "BC");
	    MakeSignature.signDetached(appearance, digest, signature, new Certificate[] { getCertificateChain() }, null, null, tsc, 0,
	            CryptoStandard.CMS);
	}

	/**
	* @return hostname of the current server
	*/
	private String getHostname() {
	  try {
	    final InetAddress addr = InetAddress.getLocalHost();
	    return addr.getHostName();
	  } catch (final UnknownHostException e) {
	    return "localhost";
	  }

	}
	  /**
	* @return
	* @throws KeyStoreException
	*/
	  public PrivateKey getPrivateKey() throws KeyStoreException {
	    try {

	      return (PrivateKey) getKeystore().getKey(this.key_alias, this.private_key_password.toCharArray());

	    } catch (final NoSuchAlgorithmException e) {
	      throw new KeyStoreException(e);
	    } catch (final UnrecoverableKeyException e) {
	      throw new KeyStoreException(e);
	    }
	  }

	/**
	* @return
	* @throws KeyStoreException
	*/
	  public Certificate getCertificateChain() throws KeyStoreException {
	    final Certificate cert = getKeystore().getCertificate(this.certificate_chain_alias);

	    return cert;

	  }

	  private KeyStore getKeystore() throws KeyStoreException {
	    try {
	      final KeyStore ks = KeyStore.getInstance(KeyStore.getDefaultType());      
	      ks.load(new FileInputStream(this.keystore_file), this.keystore_password.toCharArray());
	      return ks;
	    } catch (final NoSuchAlgorithmException e) {
	      throw new KeyStoreException(e);
	    } catch (final CertificateException e) {
	      throw new KeyStoreException(e);
	    } catch (final FileNotFoundException e) {
	      throw new KeyStoreException(e);
	    } catch (final IOException e) {
	      throw new KeyStoreException(e);
	    }
	  }
}
