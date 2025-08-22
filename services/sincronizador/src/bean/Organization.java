/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package bean;

/**
 *
 * @author Alfredo Barrón-Rodríguez
 */
public class Organization {
    
    private String token;
    private String acronym;
    private String fullname;
    
    public Organization(){
        this.token = "";
        this.fullname = "";
    }
    
    public Organization(String  token, String  fullname){
        this.token = token;
        this.fullname = fullname;
    }

    public String getToken() {
        return token;
    }

    public void setToken(String token) {
        this.token = token;
    }
    
    public String getAcronym() {
        return acronym;
    }

    public void setAcronym(String acronym) {
        this.acronym = acronym;
    }

    public String getFullname() {
        return fullname;
    }

    public void setFullname(String fullname) {
        this.fullname = fullname;
    }

    @Override
    public String toString() {
        return "Organization{" + "token=" + token + ", acronym=" + acronym + ", fullname=" + fullname + '}';
    }
    
}
