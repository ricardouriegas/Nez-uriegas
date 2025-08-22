package logic;



import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.json.JSONException;
import org.json.JSONObject;
import bean.Catalog;
import bean.Response;
import bean.User;
import java.io.File;
import java.util.List;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
public class Api {

    public Response _loginUser(String ip, String email, String password) {
        Response response = null;
        String server = "http://" + ip + "/auth/v1/users/login";
        try {
            //String params = "email=" + email + "&password=" + password;
            JSONObject json = new JSONObject();

            json.put("user", email);
            json.put("password", password);

            response = sendPost(server, json);

        } catch (JSONException ex) {
            Logger.getLogger(Api.class.getName()).log(Level.SEVERE, null, ex);
        }
        return response;
    }

    public Response _createCatalog(String ip, User user, Catalog catalog) {
        Response response = null;
        String server = "http://" + ip + "/pub_sub/v1/catalogs/create";
        String params = "access_token=" + user.getAccesToken();
        try {
            JSONObject json = new JSONObject();

            json.put("catalogname", catalog.getName());
            json.put("dispersemode", catalog.getDispersemode());
            json.put("encryption", catalog.isEncryption());
            json.put("fathers_token", catalog.getFather());
            json.put("processed", "false");

            response = sendPost(server + "?" + params, json);

        } catch (JSONException ex) {
            Logger.getLogger(Api.class.getName()).log(Level.SEVERE, null, ex);
        }
        return response;
    }
    
    public Response _getSubCatalogs(String ip, String tokencatalog, String accessToken) {
        Response response = null;
        String server = "http://" + ip + "/pub_sub/v1/view/childs/catalog/" + tokencatalog;
        String params = "access_token=" + accessToken;
        
        response = sendGet(server + "?" + params);
        
        return response;
    }
    
    public Response sendMetadata(String ip, String path) {
        System.out.println(ip);
        String charset = "UTF-8";
        File uploadFile1 = new File(path);
        String requestURL = ip+"/includes/upload_metadata.php";
 
        try {
            MultipartUtility multipart = new MultipartUtility(requestURL, charset);
             
            multipart.addHeaderField("User-Agent", "CodeJava");
            multipart.addHeaderField("Test-Header", "Header-Value");
             
            multipart.addFilePart("mins[]", uploadFile1);
 
            List<String> response = multipart.finish();
             
            System.out.println("SERVER REPLIED:");
             
            for (String line : response) {
                System.out.println(line);
            }
        } catch (IOException ex) {
            System.err.println(ex);
            sendMetadata(ip, path);
        }
        return null;
    }

    public Response _getCatalogs(String ip, User user) {
        Response response = null;
        String server = "http://" + ip + "/pub_sub/v1/view/catalogs/user/" + user.getToken() + "/published";
        System.out.println(server);
        String params = "access_token=" + user.getAccesToken();

        response = sendGet(server + "?" + params);

        return response;
    }

    public Response _getSubscriptions(String ip, User user) {
        Response response = null;
        String server = "http://" + ip + "/pub_sub/v1/view/catalogs/user/" + user.getToken() + "/subscribed";
        String params = "access_token=" + user.getAccesToken();

        response = sendGet(server + "?" + params);

        return response;
    }

    public Response _getFiles(String ip, String tokencatalog, User user) {
        Response response = null;
        String server = "http://" + ip + "/pub_sub/v1/view/files/catalog/" + tokencatalog;
        String params = "access_token=" + user.getAccesToken();

        response = sendGet(server + "?" + params);

        return response;
    }

    public Response parseFile(String ip, String content, String filename, 
            String usrToken, String catalog) {
        Response response = null;
        String server = "http://" + ip + "/parser";
        JSONObject json = new JSONObject();

        try {
            json.put("content", content);
            json.put("filename", filename);
            json.put("usrToken", usrToken);
            json.put("catalog", catalog);
        } catch (JSONException ex) {
            Logger.getLogger(Api.class.getName()).log(Level.SEVERE, null, ex);
        }

        response = sendPost(server, json);
        return response;
    }

    public Response saveData2Geoportal(String ip, String content) {
        Response response = null;
        String server = "http://" + ip + "/add_elements.php";
        response = sendPost(server, content);
        return response;
    }
    
    public Response getPorts(String ip, String org){
        Response response = null;
        String server = "http://" + ip + "/resources/manager/get_services.php?tokenOrg=" + org;
        response = sendGet(server);
        return response;
    }

    public Response sendMin(String ip, String path) {
        String charset = "UTF-8";
        File uploadFile1 = new File(path);
        String requestURL = ip+"/includes/upload_min.php";
 
        try {
            MultipartUtility multipart = new MultipartUtility(requestURL, charset);
             
            multipart.addHeaderField("User-Agent", "CodeJava");
            multipart.addHeaderField("Test-Header", "Header-Value");
             
            multipart.addFilePart("mins[]", uploadFile1);
 
            List<String> response = multipart.finish();
             
            System.out.println("SERVER REPLIED:");
             
            for (String line : response) {
                System.out.println(line);
            }
        } catch (IOException ex) {
            System.err.println(ex);
            sendMin(ip, path);
        }
        return null;
    }

    protected Response sendGet(String http_url) {
        System.out.println("Request: " + http_url);
        //String line = "";
        URL url;
        HttpURLConnection con;
        InputStreamReader in;
        BufferedReader buff;
        StringBuilder response = null;
        Response res = new Response();
        try {
            url = new URL(http_url);
            con = (HttpURLConnection) url.openConnection();

            int responseCode = con.getResponseCode();
            System.out.println("\nSending '" + con.getRequestMethod() + "' request to URL : " + http_url);
            System.out.println("Response Code : " + responseCode);
            res.setCode(responseCode);

            response = new StringBuilder();
            //if (responseCode == HttpURLConnection.HTTP_OK) {
            in = new InputStreamReader(con.getInputStream(), "utf-8");
            buff = new BufferedReader(in);
            String line = "";
            while ((line = buff.readLine()) != null) {
                response.append(line);
            }
            buff.close();
            System.out.println("" + response.toString());
            //} else {
            //System.out.println(con.getResponseMessage());
            //response = null;
            //}
            con.disconnect();
        } catch (MalformedURLException e) {
        } catch (IOException ex) {
        }
        //System.out.println("Response: " + line);
        //System.out.println();
        res.setData(response.toString());
        return res;
    }

    protected Response sendPost(String http_url, String json) {
        System.out.println("Request: " + http_url);
        //String line = "";
        URL url;
        HttpURLConnection con;
        InputStreamReader in;
        OutputStreamWriter out;
        BufferedReader buff;
        StringBuilder response = null;
        Response res = new Response();
        try {
            url = new URL(http_url);
            con = (HttpURLConnection) url.openConnection();
            
            con.setConnectTimeout(5000);
            con.setReadTimeout(10000);
            con.setRequestMethod("POST");
            con.setRequestProperty("Content-Type", "application/json; charset=UTF-8");
            con.setDoOutput(true);

            out = new OutputStreamWriter(con.getOutputStream());
            out.write(json);
            out.flush();

            int responseCode = con.getResponseCode();
            System.out.println("\nSending 'POST' request to URL : " + http_url);
            System.out.println("Post parameters : " + json);
            System.out.println("Response Code : " + responseCode);
            res.setCode(responseCode);

            response = new StringBuilder();
            //if (responseCode == HttpURLConnection.HTTP_OK) {
            in = new InputStreamReader(con.getInputStream(), "utf-8");
            buff = new BufferedReader(in);
            String line = "";
            while ((line = buff.readLine()) != null) {
                response.append(line);
            }
            buff.close();
            System.out.println("xx" + response.toString());
            //} else {
            //    System.out.println(con.getResponseMessage());
            //}

            con.disconnect();
        } catch (MalformedURLException e) {
        } catch (IOException ex) {
        }
        //System.out.println("Response: " + line);
        //System.out.println();
        res.setData(response.toString());
        return res;
    }

    protected Response sendPost(String http_url, JSONObject json) {
        System.out.println("Request: " + http_url);
        //String line = "";
        URL url;
        HttpURLConnection con;
        InputStreamReader in;
        OutputStreamWriter out;
        BufferedReader buff;
        StringBuilder response = null;
        Response res = new Response();
        try {
            url = new URL(http_url);
            con = (HttpURLConnection) url.openConnection();

            con.setRequestMethod("POST");
            con.setRequestProperty("Content-Type", "application/json; charset=UTF-8");
            con.setDoOutput(true);

            out = new OutputStreamWriter(con.getOutputStream());
            out.write(json.toString());
            out.flush();

            int responseCode = con.getResponseCode();
            System.out.println("\nSending 'POST' request to URL : " + http_url);
            System.out.println("Post parameters : " + json.toString());
            System.out.println("Response Code : " + responseCode);
            res.setCode(responseCode);

            response = new StringBuilder();
            //if (responseCode == HttpURLConnection.HTTP_OK) {
            in = new InputStreamReader(con.getInputStream(), "utf-8");
            buff = new BufferedReader(in);
            String line = "";
            while ((line = buff.readLine()) != null) {
                response.append(line);
            }
            buff.close();
            System.out.println("" + response.toString());
            //} else {
            //    System.out.println(con.getResponseMessage());
            //}

            con.disconnect();
        } catch (MalformedURLException e) {
        } catch (IOException ex) {
        }
        //System.out.println("Response: " + line);
        //System.out.println();
        res.setData(response.toString());
        return res;
    }

}
