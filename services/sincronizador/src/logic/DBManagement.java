/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package logic;

import bean.User;
import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import org.apache.commons.codec.digest.DigestUtils;

/**
 *
 * @author domizzi
 */
public class DBManagement {

    public static String sURL = "jdbc:derby:data;create=true";

    private Connection conn;

    public DBManagement() throws SQLException {
        conn = DriverManager.getConnection(sURL);
    }

    public boolean createTable() throws SQLException {
        PreparedStatement tabla = this.conn.prepareStatement("CREATE TABLE sessions "
                + "(idsession varchar(500) not null, tokenuser varchar(500), apikey varchar(500),"
                + "accesToken varchar(500), username varchar(500), org varchar(100), isAlive int, timestamp timestamp)");
        return tabla.execute();
    }

    public boolean isTableExist(String sTablename) throws SQLException {
        if (this.conn != null) {
            DatabaseMetaData dbmd = this.conn.getMetaData();
            ResultSet rs = dbmd.getTables(null, null, sTablename.toUpperCase(), null);
            if (rs.next()) {
                System.out.println("Table " + rs.getString("TABLE_NAME") + "already exists !!");
                return true;
            } else {
                System.out.println("Write your create table function here !!!");
                return false;
            }
        }
        return false;
    }

    public boolean insertSession(User objUser) throws SQLException {
        if(!this.isTableExist("sessions")){
            createTable();
        }
        String userHash = DigestUtils.sha3_256Hex(objUser.getUsername());
        Statement carga = this.conn.createStatement();
        //carga.addBatch("INSERT INTO sessions VALUES ('"+userHash+"', 1, now())");
        return carga.execute("INSERT INTO sessions VALUES ('" + userHash + "', '" + objUser.getToken()+ "', "
                + "'" + objUser.getApiKey()+ "', '" + objUser.getAccesToken()+ "', '" + objUser.getUsername()+
                "', '" + objUser.getOrg().getAcronym()+ "' , 1, CURRENT_TIMESTAMP)");
    }

    public User checkSession() throws SQLException {
        if(!this.isTableExist("sessions")){
            createTable();
        }
        Statement carga = this.conn.createStatement();
        User u = new User();
        ResultSet results = carga.executeQuery("select isAlive, tokenuser, apikey, accesToken, username, org from sessions order by timestamp desc");

        int isAlive = 0;

        while (results.next()) {
            u.setAccesToken(results.getString("accesToken"));
            u.setApiKey(results.getString("apikey"));
            u.setToken(results.getString("tokenuser"));
            u.setUsername(results.getString("username"));
            u.setSessionIsActive(results.getInt("isAlive"));
            u.getOrg().setAcronym(results.getString("org"));
            u.getOrg().setFullname(results.getString("org"));
            break;
        }

        return u;
    }

    public boolean closeSession(User objUser) throws SQLException {
        if(!this.isTableExist("sessions")){
            createTable();
        }
        String userHash = DigestUtils.sha3_256Hex(objUser.getUsername());
        Statement carga = this.conn.createStatement();
        //carga.addBatch("INSERT INTO sessions VALUES ('"+userHash+"', 1, now())");
        return carga.execute("INSERT INTO sessions VALUES ('" + userHash + "', '" + objUser.getToken()+ "', "
                + "'" + objUser.getApiKey()+ "', '" + objUser.getAccesToken()+ "', '" + objUser.getUsername()+
                "', '" + objUser.getOrg().getAcronym()+ "' , 0, CURRENT_TIMESTAMP)");
    }

    public void closeDB() throws SQLException {
        this.conn.close();
    }

}
