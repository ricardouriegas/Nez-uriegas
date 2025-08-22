/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package bean;

/**
 *
 * @author lti
 */
public class User {

    private String token;
    private String username;
    private String email;
    private String apiKey;
    private String accesToken;
    private boolean isAdmin;
    private boolean isActive;
    private Organization org;
    private int sessionIsActive;
    
    public User(){
        this.sessionIsActive = 0;
        this.org = new Organization();
    }
    

    public String getToken() {
        return token;
    }

    public void setToken(String token) {
        this.token = token;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getApiKey() {
        return apiKey;
    }

    public void setApiKey(String apiKey) {
        this.apiKey = apiKey;
    }

    public String getAccesToken() {
        return accesToken;
    }

    public void setAccesToken(String accesToken) {
        this.accesToken = accesToken;
    }

    public boolean isIsAdmin() {
        return isAdmin;
    }

    public void setIsAdmin(boolean isAdmin) {
        this.isAdmin = isAdmin;
    }

    public boolean getIsactive() {
        return isActive;
    }

    public void setIsActive(boolean isActive) {
        this.isActive = isActive;
    }

    public Organization getOrg() {
        return org;
    }

    public void setOrg(Organization org) {
        this.org = org;
    }

    public int isSessionIsActive() {
        return sessionIsActive;
    }

    public void setSessionIsActive(int sessionIsActive) {
        this.sessionIsActive = sessionIsActive;
    }
    
    

    @Override
    public String toString() {
        return "User{" + "token=" + token + ", username=" + username + ", email=" + email + ", apiKey=" + apiKey + ", accesToken=" + accesToken + ", isAdmin=" + isAdmin + ", isActive=" + isActive + ", org=" + org + '}';
    }

}
