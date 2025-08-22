package sincronizador;

import bean.User;
import bean.Response;
import bean.Catalog;
import java.io.File;
import logic.Api;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.TreeMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.stream.Stream;

import logic.ExecuteCommand;
import logic.UtilFile;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

/**
 *
 * @author Genaro
 */
public class Manager {

    private final String ipGateway;
    private final Api api;
    private final User objUser;
    private final String workdir;

    private TreeMap<String, Catalog> publications;
    private TreeMap<String, Catalog> subscriptions;

    public static String UPLOAD = "java -jar -Xms2g -Xmx1g Upload.jar ";
    public static String DOWNLOAD = "java -jar -Xms2g -Xmx2g Download.jar ";

    public Manager(String ipGateway, String ipMetadata, User objUser, String workdir) {
        this.ipGateway = ipGateway;
        this.objUser = objUser;
        this.api = new Api();
        this.workdir = workdir;
        this.publications = new TreeMap<>();
        this.subscriptions = new TreeMap<>();
    }

    public TreeMap<String, Catalog> getPublications() {
        return publications;
    }

    public void setPublications(TreeMap<String, Catalog> publications) {
        this.publications = publications;
    }

    public TreeMap<String, Catalog> getSubscriptions() {
        return subscriptions;
    }

    public void setSubscriptions(TreeMap<String, Catalog> subscriptions) {
        this.subscriptions = subscriptions;
    }

    public TreeMap<String, Catalog> getSubscriptionsFromServer() throws Exception {
        Response res_catalog;
        res_catalog = api._getSubscriptions(ipGateway, objUser);
        return getCatalogs(res_catalog, this.subscriptions);
    }

    private TreeMap<String, Catalog> getCatalogs(Response res_catalog, TreeMap<String, Catalog> catalogsMap) throws Exception {
        String strJson;
        JSONObject catalogsObj;
        JSONArray catalogs;
        JSONObject catObj;
        Catalog cat;

        int cores = (int) Runtime.getRuntime().availableProcessors() / 2 + 1;

        if (res_catalog.getCode() == 200) {
            strJson = res_catalog.getData();
            if (!strJson.isEmpty()) {
                try {

                    catalogsObj = new JSONObject(res_catalog.getData());
                    catalogs = catalogsObj.getJSONArray("data");

                    for (int i = 0; i < catalogs.length(); i++) {
                        catObj = (JSONObject) catalogs.get(i);
                        System.out.println("\nSUUUB" + catObj.getString("namecatalog") + "\n");
                        cat = new Catalog();
                        cat.setToken(catObj.getString("tokencatalog"));
                        cat.setName(catObj.getString("namecatalog"));
                        cat.setDispersemode(catObj.getString("dispersemode"));
                        cat.setEncryption(catObj.getBoolean("encryption"));
                        cat.setFather(catObj.getString("father"));
                        cat.setPath(this.workdir + "/" + objUser.getUsername() + "/" + cat.getName());

                        cat.getParametersUpload().add("java");
                        cat.getParametersUpload().add("-jar");
                        cat.getParametersUpload().add("-Xms2g");
                        cat.getParametersUpload().add("-Xmx2g");
                        cat.getParametersUpload().add("Upload.jar");
                        cat.getParametersUpload().add(objUser.getToken());
                        cat.getParametersUpload().add(objUser.getApiKey());
                        cat.getParametersUpload().add(cat.getToken());
                        cat.getParametersUpload().add(cat.getDispersemode());
                        cat.getParametersUpload().add("bob");
                        cat.getParametersUpload().add("2");
                        cat.getParametersUpload().add(cat.getPath());
                        cat.getParametersUpload().add(objUser.getOrg().getAcronym());
                        cat.getParametersUpload().add(Boolean.toString(cat.isEncryption()));
                        cat.getParametersUpload().add(objUser.getAccesToken());
                        cat.getParametersUpload().add("false");
                        cat.getParametersUpload().add("false");
                        cat.getParametersUpload().add(Integer.toString(cores));

                        cat.getParametersDownload().add("java");
                        cat.getParametersDownload().add("-jar");
                        cat.getParametersDownload().add("-Xms2g");
                        cat.getParametersDownload().add("-Xmx2g");
                        cat.getParametersDownload().add("Download.jar");
                        cat.getParametersDownload().add(objUser.getToken());
                        cat.getParametersDownload().add(objUser.getApiKey());
                        cat.getParametersDownload().add(cat.getToken());
                        cat.getParametersDownload().add("2");
                        cat.getParametersDownload().add("1");
                        cat.getParametersDownload().add(objUser.getOrg().getAcronym());
                        cat.getParametersDownload().add(cat.getPath());
                        cat.getParametersDownload().add(objUser.getAccesToken());
                        cat.getParametersDownload().add("false");
                        cat.getParametersDownload().add("false");
                        cat.getParametersDownload().add(Integer.toString(cores));

                        //UtilFile.createDir(cat.getPath());
                        
                        if (!UtilFile.createDir(cat.getPath())) {
                            throw new Exception("Couldn't create workdir for catalog " + cat.getPath());
                        }
                        

                        catalogsMap.putAll(this.getSubCatalogs(cat));
                        catalogsMap.put(cat.getName(), cat);

                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }
        return catalogsMap;
    }

    public TreeMap<String, Catalog> getPublicationsFromServer() throws Exception {
        Response res_catalog;
        res_catalog = api._getCatalogs(ipGateway, objUser);
        return getCatalogs(res_catalog, this.publications);
    }

    public TreeMap<String, Catalog> getSubCatalogs(Catalog catalog) {
        Response responseFiles = api._getSubCatalogs(ipGateway, catalog.getToken(), objUser.getAccesToken());
        TreeMap<String, Catalog> subcats = new TreeMap<>();
        TreeMap<String, Catalog> subcatsAux = new TreeMap<>();
        int cores = Runtime.getRuntime().availableProcessors() / 2;
        if (responseFiles.getCode() == 200) {
            try {
                JSONObject responseJSON = new JSONObject(responseFiles.getData());
                JSONArray ArrayFiles = responseJSON.getJSONArray("data");
                JSONObject catObj;

                for (int i = 0; i < ArrayFiles.length(); i++) {
                    catObj = (JSONObject) ArrayFiles.get(i);
                    /*Catalog cat = new Catalog(JSONFile.getString("namecatalog"),
                            JSONFile.getString("tokencatalog"),
                            JSONFile.getString("father"));*/
                    //System.out.println("\nSUUUB" + catObj.getString("namecatalog") + "\n");
                    Catalog cat = new Catalog();
                    cat.setToken(catObj.getString("tokencatalog"));
                    cat.setName(catObj.getString("namecatalog"));
                    cat.setDispersemode(catObj.getString("dispersemode"));
                    cat.setEncryption(catObj.getBoolean("encryption"));
                    cat.setFather(catObj.getString("father"));

                    cat.setPath(this.workdir + "/" + objUser.getUsername() + "/" + cat.getName());

                    cat.getParametersUpload().add("java");
                    cat.getParametersUpload().add("-jar");
                    cat.getParametersUpload().add("-Xms2g");
                    cat.getParametersUpload().add("-Xmx2g");
                    cat.getParametersUpload().add("Upload.jar");
                    cat.getParametersUpload().add(objUser.getToken());
                    cat.getParametersUpload().add(objUser.getApiKey());
                    cat.getParametersUpload().add(cat.getToken());
                    cat.getParametersUpload().add(cat.getDispersemode());
                    cat.getParametersUpload().add("bob");
                    cat.getParametersUpload().add("2");
                    cat.getParametersUpload().add(cat.getPath());
                    cat.getParametersUpload().add(objUser.getOrg().getAcronym());
                    cat.getParametersUpload().add(Boolean.toString(cat.isEncryption()));
                    cat.getParametersUpload().add(objUser.getAccesToken());
                    cat.getParametersUpload().add("false");
                    cat.getParametersUpload().add("false");
                    cat.getParametersUpload().add(Integer.toString(cores));

                    cat.getParametersDownload().add("java");
                    cat.getParametersDownload().add("-jar");
                    cat.getParametersDownload().add("-Xms2g");
                    cat.getParametersDownload().add("-Xmx2g");
                    cat.getParametersDownload().add("Download.jar");
                    cat.getParametersDownload().add(objUser.getToken());
                    cat.getParametersDownload().add(objUser.getApiKey());
                    cat.getParametersDownload().add(cat.getToken());
                    cat.getParametersDownload().add("2");
                    cat.getParametersDownload().add("1");
                    cat.getParametersDownload().add(objUser.getOrg().getAcronym());
                    cat.getParametersDownload().add(cat.getPath());
                    cat.getParametersDownload().add(objUser.getAccesToken());
                    cat.getParametersDownload().add("false");
                    cat.getParametersDownload().add("false");
                    cat.getParametersDownload().add(Integer.toString(cores));

                    UtilFile.createDir(cat.getPath());

                    if (!UtilFile.createDir(cat.getPath())) {
                        throw new Exception("Couldn't create workdir for catalog " + cat.getName());
                    }

                    subcatsAux.put(cat.getName(), cat);
                    subcats.put(cat.getName(), cat);
                    
                    
                    subcats.putAll(this.getSubCatalogs(cat));
                }
            } catch (Exception ex) {
                ex.printStackTrace();

            }
        }
        
        catalog.setCatalogs(subcatsAux);

        return subcats;

    }

    public void uploadCatalog(Catalog cat) {
        System.out.println("Uploading catalogo " + cat.getName());
        /*robotUpload = new RobotUploader(objUser.getToken(), objUser.getApiKey(), cat.getPath(), cat.getToken(),
                cat.getDispersemode(), cat.isEncryption(), "bob", 10, objUser.getOrg().getAcronym(),
                objUser.getAccesToken(), true, false);
        robotUpload.upload();*/
        System.out.println(cat.getParametersUpload());
        try {
            System.out.println(cat.getParametersUpload());
            ExecuteCommand.executeCommand(cat.getParametersUpload());
        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
        }
    }

    public void downloadCatalog(Catalog cat) {
        System.out.println("Descargando catalogo " + cat.getName());
        System.out.println(cat.getParametersDownload());
        try {
            ExecuteCommand.executeCommand(cat.getParametersDownload());
        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
        }
    }

    public void checkForNewCatalogs() {

        File dir = new File(this.workdir + "/" + objUser.getUsername());
        //File[] dirsInPath = dir.listFiles(File::isDirectory);
        Response r;
        Catalog cat;
        String pathStr = this.workdir + "/" + objUser.getUsername();
        String father = "/";
        String separator = System.getProperty("file.separator");
        Path workPath = Paths.get(pathStr);
        
        HashMap<String, String> createdTem = new HashMap<>();

        try ( Stream<Path> pathStream = Files.walk(workPath)
                .filter(Files::isDirectory)) {
            
            for (Path file : (Iterable<Path>) pathStream::iterator) {
                
                if (!file.toString().equals(workPath.toString())) {
                    String name = file.toString().replace(workPath.toString() + separator, "").replace(separator, "/");
                    System.out.println("AAAAAAA " + (!this.publications.containsKey(name) && !this.subscriptions.containsKey(name)));
                    
                    if (!this.publications.containsKey(name) && !this.subscriptions.containsKey(name)) {
                        System.out.println("AAAAAAA " + (!this.publications.containsKey(name) && !this.subscriptions.containsKey(name)));
                        int i = name.lastIndexOf('/');
                        System.out.println(i);
                        if(i > 0){
                            String[] a =  {name.substring(0, i), name.substring(i)};
                            father = a[0];
                        }

                        cat = new Catalog();
                        cat.setName(name);
                        cat.setDispersemode("SINGLE");
                        cat.setEncryption(true);
                        
                        System.out.println("FATHER " + father + "\t" + name);
                        
                        if(this.publications.containsKey(father)){
                            System.out.println("III");
                            cat.setFather(this.publications.get(father).getToken());
                            System.out.println("OOOO");
                        }else if(this.subscriptions.containsKey(father)){
                            System.out.println("UUUU");
                            cat.setFather(this.subscriptions.get(father).getToken());
                        }else if(createdTem.containsKey(father)){
                            System.out.println("XXXX");
                            cat.setFather(createdTem.get(father));
                        }else{
                            System.out.println("YYYY");
                             cat.setFather(father);
                        }
                        
                        
                        r = this.api._createCatalog(ipGateway, objUser, cat);
                        if (r.getCode() == 201) {
                            System.out.println("Catalog " + cat.getName() + " created");
                            JSONObject responseJSON = new JSONObject(r.getData());
                            String tokenCat = responseJSON.getString("tokencatalog");
                            System.out.println("TOKEN"  + tokenCat);
                            createdTem.put(name, tokenCat);
                        }
                        father = "/";
                       
                    }
                }
            }
            System.out.println("Termino");
        } catch (IOException ex) {
            ex.printStackTrace();
        } catch (JSONException ex) {
            ex.printStackTrace();
        }

        /*for (String f : dirsInPath) {
            System.out.println("AAAAAAA " + f);
            /*if (!this.publications.containsKey(f.getName()) && !this.subscriptions.containsKey(f.getName())) {
                    cat = new Catalog();
                    cat.setName(f.getName());
                    cat.setDispersemode("SINGLE");
                    cat.setEncryption(true);
                    cat.setFather("/");
                    r = this.api._createCatalog(ipGateway, objUser, cat);
                    if (r.getCode() == 200) {
                        System.out.println("Catalog " + cat.getName() + " created");
                    }
                }
        }*/
    }
} 