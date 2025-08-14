/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package logic;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.concurrent.TimeUnit;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author domizzi
 */
public class ExecuteCommand {

    public static String executeCommands(String[] commands) {
        Runtime rt;
        String aux;
        Process proc;
        BufferedReader stdInput, stdError;
        StringBuilder res;

        rt = Runtime.getRuntime();
        res = new StringBuilder();
        try {
            proc = rt.exec(commands);
            stdInput = new BufferedReader(new InputStreamReader(proc.getInputStream()));
            stdError = new BufferedReader(new InputStreamReader(proc.getErrorStream()));
            while ((aux = stdInput.readLine()) != null) {
                res.append(aux).append("\n");
            }
        } catch (IOException ex) {
            Logger.getLogger(ExecuteCommand.class.getName()).log(Level.SEVERE, null, ex);
        }
        return res.toString();
    }

    public static void executeCommand(ArrayList<String> parameters) throws IOException, InterruptedException {
        System.out.println("EJECUTANDO... " );
        ProcessBuilder builder = new ProcessBuilder(parameters);

        // DEBUG : Build and printout the commands...
        // 
        String lstrCommand = "";
        for (int theIdx = 0; theIdx < parameters.size(); theIdx++) {
            if (theIdx == 0) {
                lstrCommand = lstrCommand + parameters.get(theIdx);
            } else {
                lstrCommand = lstrCommand + " " + parameters.get(theIdx);
            }
            //System.out.println(" Building Command[] [" + parameters.get(theIdx) + "]");
        }

        System.out.println(" \n\nRunning Command[] [" + lstrCommand + "]\n\n");

        builder.redirectErrorStream(true);

        Process process = builder.start();

        InputStreamReader istream = new InputStreamReader(process.getInputStream());
        BufferedReader br = new BufferedReader(istream, 10);

        String line;
        while ((line = br.readLine()) != null) {
            System.out.println(line);
        }

        //execute = !process.waitFor(5, TimeUnit.SECONDS);
    }
}
