/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package sincronizador;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.Statement;
import org.apache.commons.codec.digest.DigestUtils;

/**
 *
 * @author domizzi
 */
public class Test {

    public static void main(String[] args) {
        Connection con = null;

        String sURL = "jdbc:derby:data;create=true";

        try {

            con = DriverManager.getConnection(sURL);

            // Creamos la tabla
            //PreparedStatement tabla = con.prepareStatement("CREATE TABLE sessions (idsession varchar(500) not null, isAlive int, timestamp timestamp)");
            //tabla.execute();
            String userHash = DigestUtils.sha3_256Hex("test");
            System.out.println(userHash);

            // Insertamos datos	      
            Statement carga = con.createStatement();
            //carga.addBatch("INSERT INTO sessions VALUES ('" + userHash + "', 1, CURRENT_TIMESTAMP)");
            /*carga.addBatch("INSERT INTO country VALUES ('France')");
          carga.addBatch("INSERT INTO country VALUES ('United States')");
          carga.addBatch("INSERT INTO country VALUES ('Brazil')");
          carga.addBatch("INSERT INTO country VALUES ('Japan')");	*/
            /*carga.executeBatch();
            carga.close();
            carga = con.createStatement();*/
            ResultSet results = carga.executeQuery("select * from sessions order by timestamp desc");

            ResultSetMetaData rsmd = results.getMetaData();
            int numberCols = rsmd.getColumnCount();
            for (int i = 1; i <= numberCols; i++) {
                //print Column Names
                System.out.print(rsmd.getColumnLabel(i) + "\t\t");
            }

            System.out.println("\n-------------------------------------------------");

            while (results.next()) {
                String id = results.getString(1);
                String restName = results.getString(2);
                String cityName = results.getString(3);
                System.out.println(id + "\t\t" + restName + "\t\t" + cityName);
            }
            results.close();
            carga.close();

        } catch (Exception e) {
            System.out.println("Error en la conexión:" + e.toString());
        }
    }
}
