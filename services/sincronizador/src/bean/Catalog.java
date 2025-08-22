/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package bean;

import java.util.ArrayList;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Objects;
import java.util.TreeMap;
import sincronizador.App;

/**
 *
 * @author lti
 */
public class Catalog {
    
    private String token;
    private String name;
    private String path;
    private String dispersemode;
    private boolean encryption;
    private String father;
    private String commandUpload;
    private String commandDownload;
    private ArrayList<String> parametersUpload;
    private ArrayList<String> parametersDownload;
    private int files;
    private TreeMap<String, Catalog> catalogs;
    
    public Catalog(){
        this.catalogs = new TreeMap();
        this.parametersUpload = new ArrayList<>();
        this.parametersDownload = new ArrayList<>();
    }

    public TreeMap<String, Catalog> getCatalogs() {
        return catalogs;
    }

    public ArrayList<String> getParametersUpload() {
        return parametersUpload;
    }

    public void setParametersUpload(ArrayList<String> parametersUpload) {
        this.parametersUpload = parametersUpload;
    }

    public ArrayList<String> getParametersDownload() {
        return parametersDownload;
    }

    public void setParametersDownload(ArrayList<String> parametersDownload) {
        this.parametersDownload = parametersDownload;
    }
    
    

    public void setCatalogs(TreeMap<String, Catalog> catalogs) {
        this.catalogs = catalogs;
    }
    
    public String getToken() {
        return token;
    }
    public void setToken(String token) {
        this.token = token;
    }

    public String getPath() {
        System.out.println(path);
        return this.path;
    }

    public void setPath(String path) {
        this.path = path;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }
    
    public String getDispersemode() {
        return dispersemode;
    }

    public void setDispersemode(String dispersemode) {
        this.dispersemode = dispersemode;
    }

    public boolean isEncryption() {
        return encryption;
    }

    public void setEncryption(boolean encryption) {
        this.encryption = encryption;
    }

    public String getFather() {
        return father;
    }

    public void setFather(String father) {
        this.father = father;
    }

    public String getCommandUpload() {
        return commandUpload;
    }

    public void setCommandUpload(String commandUpload) {
        this.commandUpload = commandUpload;
    }

    public String getCommandDownload() {
        return commandDownload;
    }

    public void setCommandDownload(String commandDownload) {
        this.commandDownload = commandDownload;
    }

    public int getFiles() {
        return files;
    }

    public void setFiles(int files) {
        this.files = files;
    }
    
    /*public String getHierarchyPath(){
        
        for(Entry<String,Catalog> e:App.catalogs.entrySet()){
            if(e.getValue().getToken().equals(this.father)){
                return e.getValue().getHierarchyPath() + this.getName();
            }
        }
        return this.getName();
    }*/
    
    @Override
    public int hashCode() {
        int hash = 5;
        hash = 47 * hash + Objects.hashCode(this.token);
        return hash;
    }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) {
            return true;
        }
        if (obj == null) {
            return false;
        }
        if (getClass() != obj.getClass()) {
            return false;
        }
        final Catalog other = (Catalog) obj;
        if (!Objects.equals(this.token, other.token)) {
            return false;
        }
        return true;
    }

    @Override
    public String toString() {
        return name;
    }
    
}
