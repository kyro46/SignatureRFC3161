import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.security.KeyStoreException;
import java.security.SignatureException;

import com.lowagie.text.DocumentException;

public class SignPdfMain {
			
	private String orgFile;
	private String key_alias;
	private String certificate_chain_alias;
	private String keystore_password;
	private String private_key_password;
	private String keystore_file;
	
	public static void main(String[] args){		
		SignPdfMain sign = new SignPdfMain();
		sign.init(args);
		sign.sign();			
	}	

	private void init(String[] args){
		try {
			// pfad zur pdf, die signiert werden soll
			this.orgFile = args[0];
			this.key_alias = args[1]; 	
			this.certificate_chain_alias = args[2]; 	
			this.keystore_password = args[3]; 	
			this.private_key_password = args[4]; 
			this.keystore_file = args[5];
			/*
			System.out.println("********************");
			System.out.println(args[0]);
			System.out.println(args[1]);
			System.out.println(args[2]);
			System.out.println(args[3]);
			System.out.println(args[4]);
			System.out.println(args[5]);
			System.out.println("********************");
			*/
		} catch (Exception e){
			e.printStackTrace();
		}
	}
	
	private void sign(){					
		try {					
			// orginal File
			File file = new File(this.orgFile);
			// signed pdf			
			File sign = new File(file.getAbsolutePath().replace(".pdf", "SIGN.pdf"));
			// keystore
			File keystore = new File(this.keystore_file);
			
			// fill signatureInfos with data 
		    final SignatureInfos infos = new SignatureInfos();
		    infos.setKeystoreFile(keystore.getAbsolutePath());
		    infos.setKeyAlias(this.key_alias);
		    infos.setCertificateChainAlias(this.certificate_chain_alias);
		    infos.setKeystorePassword(this.keystore_password);
		    infos.setPrivateKeyPassword(this.private_key_password);
		    
		    SignPdf.signAndTimestamp(new FileInputStream(new File(file.getAbsolutePath())),
		    		new FileOutputStream(sign), infos);		    		    		
		} catch (IOException e) {			
			e.printStackTrace();			
		} catch (SignatureException e) {
			e.printStackTrace();			
		} catch (KeyStoreException e) {			
			e.printStackTrace();			
		} catch (DocumentException e) {			
			e.printStackTrace();			
		} 									
	}				
}
