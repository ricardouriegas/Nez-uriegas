package logic;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Clase con los metoos basicos para el manejo de archivos con texti plano
 *
 * @author Dante Domizzi
 */
public class UtilFile {

    /**
     * Método que lee el texto de un archivo plano
     *
     * @param filename Nombre del archivo
     * @throws IOException Lanza una excepción cuando hay problemas con la
     * lectura del archivo
     * @return Contenido del archivo en un String
     */
    public static String read(String filename) throws IOException {
        StringBuffer sb = new StringBuffer();
        FileInputStream fis = new FileInputStream(filename);
        int c;

        while ((c = fis.read()) != -1) {
            sb.append((char) c);
        }
        fis.close();
        return sb.toString();
    }

    public static String read(File file) throws FileNotFoundException, IOException {
        StringBuffer sb = new StringBuffer();
        FileInputStream fis = new FileInputStream(file);
        int c;

        while ((c = fis.read()) != -1) {
            sb.append((char) c);
        }
        fis.close();
        return sb.toString();
    }

    /**
     * Método que escribe un String en un archivo.
     *
     * @param filename Nombre del archivo
     * @param content String con el contenido del archivo
     * @param append Indica si debe de reemplazar el contenido del archivo o
     * agregarlo al final.
     * @throws IOException Lanza una excepción cuando hay problemas con la
     * escritura del archivo
     */
    public static void write(String filename, String content, boolean append) throws IOException {
        FileOutputStream fos = new FileOutputStream(filename, append);
        fos.write(content.getBytes());
        fos.close();
    }

    /**
     * Método que lee un archivo separado por un delimitador y lo almacena en
     * una matriz.
     *
     * @param filename Nombre del archivo
     * @param delimiter Delimitador del archivo
     * @param hasHeader Indica si el archivo tienen encabezado o no
     * @throws IOException Lanza una excepción cuando hay problemas con la
     * lectura del archivo
     * @return Matriz con los datos leídos del archivo
     */
    public static String[][] readAsMatrix(String filename, String delimiter, boolean hasHeader) throws IOException {
        String[][] matrix = null;
        String text = UtilFile.read(filename);
        String[] textRows = text.split("\n");
        int nCols = textRows[0].split(delimiter).length;
        int nRows = textRows.length;
        matrix = new String[hasHeader ? nRows - 1 : nRows][nCols];

        for (int i = hasHeader ? 1 : 0, j = 0; i < nRows; i++, j++) {
            matrix[j] = textRows[i].split(delimiter);
        }

        return matrix;
    }

    /**
     * Método que escribe un matriz en un archivo separado por un delimitador
     * indicado por el usuario.
     *
     * @param filename Nombre del archivo
     * @param matrix Matrix que debe ser escrita en el archivo
     * @param header Encabezados del archivo
     * @param delimiter Delimitador del archivo
     * @throws IOException Lanza una excepción cuando hay problemas con la
     * escritura del archivo
     */
    public static void writeMatrix(String filename, String[][] matrix, String[] header, String delimiter) throws IOException {
        StringBuffer sb = new StringBuffer();
        if (header != null) {
            for (int i = 0; i < header.length - 1; i++) {
                sb.append(header[i]);
                sb.append(delimiter);
            }
            sb.append(header[header.length - 1]);
            sb.append("\n");
        }
        for (int i = 0; i < matrix.length; i++) {
            for (int j = 0; j < matrix[0].length - 1; j++) {
                sb.append(matrix[i][j]);
                sb.append(delimiter);
            }
            sb.append(matrix[i][matrix[0].length - 1]);
            sb.append("\n");
        }
        write(filename, sb.toString(), false);
    }

    public static Map<String, List<String>> getDirContent(final File folder) {
        Map<String, List<String>> dirContent = new HashMap<>();
        List<String> files, directories; 
        
        directories = new ArrayList<>();
        files = new ArrayList<>();
        
        
        for (final File fileEntry : folder.listFiles()) {
            if (fileEntry.isDirectory()) {
                directories.add(fileEntry.getName());
            } else {
                files.add(fileEntry.getName());
            }
        }
        
        dirContent.put("files", files);
        dirContent.put("directories", directories);
        
        return dirContent;
    }

    public static Boolean createDir(String path){
        File folder;

        folder = new File(path);
        System.out.println(path);
        if (!folder.exists()) {
            return folder.mkdirs();
        }
        return true;
    }
}
