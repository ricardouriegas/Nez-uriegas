/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package logic;

import bean.Response;
import java.io.IOException;
import java.util.Iterator;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.json.JSONException;
import org.json.JSONObject;

/**
 *
 * @author Alfredo Barrón-Rodríguez
 */
public class Routes {
    
    //public static String GATEWAY = "disys0.tamps.cinvestav.mx:4747";
    //public static String METADATA = "disys0.tamps.cinvestav.mx:47013";

    //public static String GATEWAY = "148.247.204.104:8085";
    //public static String METADATA = "148.247.204.104:47013";
    //public static get
    private String gatewayRoute;
    private String metadataRoute;
    private String frontend;
    
    public Routes() throws IOException{
        String conf = UtilFile.read("config.db");
        String[] confs = conf.split("\n");
        metadataRoute =  confs[5].trim();
        gatewayRoute =  confs[6].trim();
        frontend =  confs[7].trim();
    }

    public String getGatewayRoute() {
        return gatewayRoute;
    }

    public void setGatewayRoute(String gatewayRoute) {
        this.gatewayRoute = gatewayRoute;
    }

    public String getMetadataRoute() {
        return metadataRoute;
    }

    public void setMetadataRoute(String metadataRoute) {
        this.metadataRoute = metadataRoute;
    }

    public String getFrontend() {
        return frontend;
    }

    public void setFrontend(String frontend) {
        this.frontend = frontend;
    }
    
    
    
    
    public static void main(String[] args){
        try {
            Routes r = new Routes();
            
        } catch (IOException ex) {
            Logger.getLogger(Routes.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
}
