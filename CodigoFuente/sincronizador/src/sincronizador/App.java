//Version 6.2
//Fecha de modificación: Jueves 22 de diciembre 2017.

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package sincronizador;

import bean.User;
import bean.Catalog;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import logic.Api;
import java.io.FileNotFoundException;
import java.util.*;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.*;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.SQLException;
import java.util.Map.Entry;
import java.util.concurrent.atomic.AtomicBoolean;
import java.util.stream.Stream;
import javax.swing.tree.DefaultMutableTreeNode;
import javax.swing.tree.DefaultTreeModel;
import javax.swing.tree.TreePath;
import logic.DBManagement;

import logic.UtilFile;
import logic.UtilOS;

/**
 *
 */
public final class App extends javax.swing.JFrame {

    //Extraer ruta.
    File verif = new File("PATH.txt");
    String path = verif.getPath();
    JFileChooser elegir = new JFileChooser();

    private Manager manager;
    private final User objUser;
    private String workdir;
    private final String ipGateway;
    private final String ipMetadata;
    private Catalog selected;
    private Catalog selectedSub;
    private FolderMaker fm;

    private ScheduledExecutorService uploadExecutor, donwloadExecutor;

    /**
     * Creates new form App
     */
    public App(String ipGateway, String ipMetadata, User objUser) throws IOException {

        this.objUser = objUser;
        this.ipGateway = ipGateway;
        this.ipMetadata = ipMetadata;

        initComponents();
        this.catalogsTree.setModel(null);
    }

    public void ejecutar() {
        setLocationRelativeTo(null);

        try {
            this.verificarCarpeta();
        } catch (IOException e) {
            e.printStackTrace();
        }
        fm = new FolderMaker(workdir + "/" + objUser.getUsername());
        this.manager = new Manager(ipGateway, ipMetadata, this.objUser, this.workdir);
        uploadExecutor = Executors.newSingleThreadScheduledExecutor();
        donwloadExecutor = Executors.newSingleThreadScheduledExecutor();
        getApi();
        getResources();
        
        
        uploadExecutor.scheduleAtFixedRate(() -> {
            this.manager.checkForNewCatalogs();
            getResources();

            this.manager.getPublications().entrySet().stream().forEach(cat -> {
                System.out.println("Subiendo catalogo " + cat.getValue().getName());
                txtStateUp.setText("Subiendo catalogo " + cat.getValue().getName());
                this.manager.uploadCatalog(cat.getValue());

                txtStateDown.setText("Descargando catalogo " + cat.getValue().getName());
                this.manager.downloadCatalog(cat.getValue());

            });
            txtStateUp.setText("Todos los catálogos han sido cargados");
            txtStateDown.setText("Todos los catálogos han sido descargados");
            
            System.out.println("\n\nAAAAAAHOLLAAA " + this.manager.getSubscriptions());
            
            this.manager.getSubscriptions().entrySet().stream().forEach(cat -> {
                
                System.out.println("Subiendo catalogo " + cat.getValue().getName());
                txtStateUp.setText("Subiendo suscripción " + cat.getValue().getName());
                this.manager.uploadCatalog(cat.getValue());

                txtStateDown.setText("Descargando suscripción " + cat.getValue().getName());
                this.manager.downloadCatalog(cat.getValue());
            });
            txtStateUp.setText("Todos los catálogos han sido cargados");
            txtStateDown.setText("Todos los catálogos han sido descargados");

        }, 0, 20, TimeUnit.SECONDS);

        /*donwloadExecutor.scheduleAtFixedRate(() -> {

            this.manager.checkForNewCatalogs();
            getResources();
            this.manager.getPublications().entrySet().stream().forEach(cat -> {
                txtStateDown.setText("Descargando catalogo " + cat.getValue().getName());
                this.manager.downloadCatalog(cat.getValue());
            });
            txtStateDown.setText("Todos los catálogos han sido descargados");
            
            this.manager.getSubscriptions().entrySet().stream().forEach(cat -> {
                txtStateDown.setText("Descargando suscripción " + cat.getValue().getName());
                this.manager.downloadCatalog(cat.getValue());
            });
        }, 0, 20, TimeUnit.SECONDS);*/
 /* uploadExecutor.scheduleAtFixedRate(() -> {
            
            
            this.manager.getPublications().entrySet().stream().forEach(cat -> {
                System.out.println("Subiendo catalogo " + cat.getValue().getName());
                txtStateUp.setText("Subiendo catalogo " + cat.getValue().getName());
                this.manager.uploadCatalog(cat.getValue());
            });
            txtStateUp.setText("Todos los catálogos han sido cargados");
            
             this.manager.getSubscriptions().entrySet().stream().forEach(cat -> {
                 System.out.println("Subiendo catalogo " + cat.getValue().getName());
                txtStateUp.setText("Subiendo suscripción " + cat.getValue().getName());
                this.manager.uploadCatalog(cat.getValue());
            });
            txtStateUp.setText("Todos los catálogos han sido cargados");
            /*this.manager.checkForNewCatalogs();
            getResources();
        }, 0, 20, TimeUnit.SECONDS);
        
        

        donwloadExecutor.scheduleAtFixedRate(() -> {

            this.manager.checkForNewCatalogs();
            getResources();
            this.manager.getPublications().entrySet().stream().forEach(cat -> {
                txtStateDown.setText("Descargando catalogo " + cat.getValue().getName());
                this.manager.downloadCatalog(cat.getValue());
            });
            txtStateDown.setText("Todos los catálogos han sido descargados");
            
            this.manager.getSubscriptions().entrySet().stream().forEach(cat -> {
                txtStateDown.setText("Descargando suscripción " + cat.getValue().getName());
                this.manager.downloadCatalog(cat.getValue());
            });
        }, 0, 20, TimeUnit.SECONDS);*/
    }

    /**
     * This method is called from within the constructor to initialize the form.
     * WARNING: Do NOT modify this code. The content of this method is always
     * regenerated by the Form Editor.
     */
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        jMenuItem1 = new javax.swing.JMenuItem();
        Principal = new javax.swing.JPanel();
        Elementos = new javax.swing.JPanel();
        Organizacion = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        sesion_lb = new javax.swing.JLabel();
        inicioU_txt = new javax.swing.JLabel();
        org_txt = new javax.swing.JLabel();
        jButton1 = new javax.swing.JButton();
        jTabbedPane4 = new javax.swing.JTabbedPane();
        jPanel2 = new javax.swing.JPanel();
        catalogos = new javax.swing.JLabel();
        logoCarpeta = new javax.swing.JLabel();
        archivos_lb = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        panelArchivos2 = new javax.swing.JScrollPane();
        list_files = new javax.swing.JList<>();
        txtCatalogSelected = new javax.swing.JLabel();
        jButton2 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        catalogsTree = new javax.swing.JTree();
        jPanel3 = new javax.swing.JPanel();
        catalogos1 = new javax.swing.JLabel();
        logoCarpeta1 = new javax.swing.JLabel();
        archivos_lb1 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        panelArchivos1 = new javax.swing.JScrollPane();
        list_subscriptions = new javax.swing.JList<>();
        txtSubSelected = new javax.swing.JLabel();
        jScrollPane3 = new javax.swing.JScrollPane();
        treeSubs = new javax.swing.JTree();
        txtStateUp = new javax.swing.JLabel();
        txtStateDown = new javax.swing.JLabel();
        jMenuBar1 = new javax.swing.JMenuBar();
        jMenu2 = new javax.swing.JMenu();
        jMenuItem2 = new javax.swing.JMenuItem();

        jMenuItem1.setText("jMenuItem1");

        setDefaultCloseOperation(javax.swing.WindowConstants.EXIT_ON_CLOSE);
        setTitle("SkyCDS");
        setCursor(new java.awt.Cursor(java.awt.Cursor.DEFAULT_CURSOR));
        setResizable(false);

        Principal.setLayout(new java.awt.CardLayout());

        Elementos.setBackground(new java.awt.Color(254, 254, 254));
        Elementos.setForeground(new java.awt.Color(255, 255, 255));
        Elementos.setName(""); // NOI18N
        Elementos.setLayout(null);

        Organizacion.setBackground(new java.awt.Color(0, 51, 51));
        Organizacion.setBorder(javax.swing.BorderFactory.createEtchedBorder());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        jLabel1.setForeground(new java.awt.Color(255, 255, 255));
        jLabel1.setText("Organización:");

        sesion_lb.setBackground(new java.awt.Color(255, 255, 255));
        sesion_lb.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        sesion_lb.setForeground(new java.awt.Color(255, 255, 255));
        sesion_lb.setText("Usuario:");

        inicioU_txt.setFont(new java.awt.Font("Tahoma", 0, 14)); // NOI18N
        inicioU_txt.setForeground(new java.awt.Color(255, 255, 255));

        org_txt.setFont(new java.awt.Font("Tahoma", 0, 14)); // NOI18N
        org_txt.setForeground(new java.awt.Color(255, 255, 255));

        jButton1.setFont(new java.awt.Font("sansserif", 1, 13)); // NOI18N
        jButton1.setForeground(new java.awt.Color(51, 51, 51));
        jButton1.setText("Etiquetar directorio");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });

        javax.swing.GroupLayout OrganizacionLayout = new javax.swing.GroupLayout(Organizacion);
        Organizacion.setLayout(OrganizacionLayout);
        OrganizacionLayout.setHorizontalGroup(
            OrganizacionLayout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(OrganizacionLayout.createSequentialGroup()
                .addContainerGap()
                .addGroup(OrganizacionLayout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(OrganizacionLayout.createSequentialGroup()
                        .addComponent(jLabel1)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(org_txt, javax.swing.GroupLayout.PREFERRED_SIZE, 232, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addGroup(OrganizacionLayout.createSequentialGroup()
                        .addComponent(sesion_lb, javax.swing.GroupLayout.PREFERRED_SIZE, 90, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(inicioU_txt, javax.swing.GroupLayout.PREFERRED_SIZE, 249, javax.swing.GroupLayout.PREFERRED_SIZE)))
                .addGap(371, 371, 371)
                .addComponent(jButton1, javax.swing.GroupLayout.PREFERRED_SIZE, 198, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(16, 16, 16))
        );
        OrganizacionLayout.setVerticalGroup(
            OrganizacionLayout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(OrganizacionLayout.createSequentialGroup()
                .addGap(14, 14, 14)
                .addComponent(jButton1, javax.swing.GroupLayout.PREFERRED_SIZE, 30, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addContainerGap(javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, OrganizacionLayout.createSequentialGroup()
                .addContainerGap(javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                .addGroup(OrganizacionLayout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(sesion_lb, javax.swing.GroupLayout.PREFERRED_SIZE, 22, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(inicioU_txt, javax.swing.GroupLayout.PREFERRED_SIZE, 23, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(OrganizacionLayout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(org_txt, javax.swing.GroupLayout.PREFERRED_SIZE, 23, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jLabel1))
                .addGap(12, 12, 12))
        );

        Elementos.add(Organizacion);
        Organizacion.setBounds(0, 0, 950, 60);

        catalogos.setBackground(new java.awt.Color(255, 255, 255));
        catalogos.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        catalogos.setForeground(new java.awt.Color(1, 1, 1));
        catalogos.setText("Catálogos");

        logoCarpeta.setIcon(new javax.swing.ImageIcon(getClass().getResource("/Imagenes/carpeta.png"))); // NOI18N

        archivos_lb.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        archivos_lb.setForeground(new java.awt.Color(1, 1, 1));
        archivos_lb.setText("Archivos en ");

        jLabel2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/Imagenes/archivo.png"))); // NOI18N

        list_files.setFont(new java.awt.Font("sansserif", 1, 13)); // NOI18N
        list_files.setEnabled(false);
        panelArchivos2.setViewportView(list_files);

        txtCatalogSelected.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        txtCatalogSelected.setForeground(new java.awt.Color(1, 1, 1));

        jButton2.setText("+");
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });

        catalogsTree.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                catalogsTreeMouseClicked(evt);
            }
        });
        jScrollPane2.setViewportView(catalogsTree);

        javax.swing.GroupLayout jPanel2Layout = new javax.swing.GroupLayout(jPanel2);
        jPanel2.setLayout(jPanel2Layout);
        jPanel2Layout.setHorizontalGroup(
            jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel2Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel2Layout.createSequentialGroup()
                        .addComponent(catalogos)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(logoCarpeta)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jButton2))
                    .addComponent(jScrollPane2, javax.swing.GroupLayout.PREFERRED_SIZE, 206, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel2Layout.createSequentialGroup()
                        .addGap(19, 19, 19)
                        .addComponent(jLabel2)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(archivos_lb, javax.swing.GroupLayout.PREFERRED_SIZE, 88, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(txtCatalogSelected, javax.swing.GroupLayout.PREFERRED_SIZE, 242, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addGroup(jPanel2Layout.createSequentialGroup()
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(panelArchivos2, javax.swing.GroupLayout.PREFERRED_SIZE, 677, javax.swing.GroupLayout.PREFERRED_SIZE)))
                .addContainerGap(19, Short.MAX_VALUE))
        );
        jPanel2Layout.setVerticalGroup(
            jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel2Layout.createSequentialGroup()
                .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel2Layout.createSequentialGroup()
                        .addGap(15, 15, 15)
                        .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addGroup(jPanel2Layout.createSequentialGroup()
                                .addGap(2, 2, 2)
                                .addComponent(jLabel2, javax.swing.GroupLayout.PREFERRED_SIZE, 15, javax.swing.GroupLayout.PREFERRED_SIZE))
                            .addComponent(catalogos)
                            .addComponent(logoCarpeta, javax.swing.GroupLayout.PREFERRED_SIZE, 17, javax.swing.GroupLayout.PREFERRED_SIZE)))
                    .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel2Layout.createSequentialGroup()
                        .addContainerGap()
                        .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(txtCatalogSelected, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.PREFERRED_SIZE, 17, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(archivos_lb, javax.swing.GroupLayout.Alignment.TRAILING)
                            .addComponent(jButton2, javax.swing.GroupLayout.Alignment.TRAILING))))
                .addGap(12, 12, 12)
                .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING, false)
                    .addComponent(panelArchivos2, javax.swing.GroupLayout.DEFAULT_SIZE, 369, Short.MAX_VALUE)
                    .addComponent(jScrollPane2, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE))
                .addContainerGap(14, Short.MAX_VALUE))
        );

        jTabbedPane4.addTab("Mis catálogos", jPanel2);

        catalogos1.setBackground(new java.awt.Color(255, 255, 255));
        catalogos1.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        catalogos1.setForeground(new java.awt.Color(1, 1, 1));
        catalogos1.setText("Suscripciones");

        logoCarpeta1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/Imagenes/carpeta.png"))); // NOI18N

        archivos_lb1.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        archivos_lb1.setForeground(new java.awt.Color(1, 1, 1));
        archivos_lb1.setText("Archivos en:");

        jLabel3.setIcon(new javax.swing.ImageIcon(getClass().getResource("/Imagenes/archivo.png"))); // NOI18N

        panelArchivos1.setViewportView(list_subscriptions);

        txtSubSelected.setFont(new java.awt.Font("sansserif", 1, 13)); // NOI18N

        treeSubs.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                treeSubsMouseClicked(evt);
            }
        });
        jScrollPane3.setViewportView(treeSubs);

        javax.swing.GroupLayout jPanel3Layout = new javax.swing.GroupLayout(jPanel3);
        jPanel3.setLayout(jPanel3Layout);
        jPanel3Layout.setHorizontalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel3Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel3Layout.createSequentialGroup()
                        .addComponent(catalogos1)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(logoCarpeta1))
                    .addComponent(jScrollPane3, javax.swing.GroupLayout.PREFERRED_SIZE, 203, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addGap(18, 18, 18)
                .addGroup(jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel3Layout.createSequentialGroup()
                        .addComponent(jLabel3)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(archivos_lb1, javax.swing.GroupLayout.PREFERRED_SIZE, 100, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(txtSubSelected, javax.swing.GroupLayout.PREFERRED_SIZE, 164, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addContainerGap(391, Short.MAX_VALUE))
                    .addGroup(jPanel3Layout.createSequentialGroup()
                        .addComponent(panelArchivos1)
                        .addGap(24, 24, 24))))
        );
        jPanel3Layout.setVerticalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel3Layout.createSequentialGroup()
                .addGroup(jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel3Layout.createSequentialGroup()
                        .addGap(9, 9, 9)
                        .addComponent(txtSubSelected, javax.swing.GroupLayout.PREFERRED_SIZE, 23, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addGroup(jPanel3Layout.createSequentialGroup()
                        .addGap(20, 20, 20)
                        .addGroup(jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(archivos_lb1)
                            .addComponent(jLabel3, javax.swing.GroupLayout.PREFERRED_SIZE, 15, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(logoCarpeta1, javax.swing.GroupLayout.PREFERRED_SIZE, 17, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(catalogos1))))
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING, false)
                    .addComponent(panelArchivos1, javax.swing.GroupLayout.DEFAULT_SIZE, 372, Short.MAX_VALUE)
                    .addComponent(jScrollPane3, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE))
                .addContainerGap(javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
        );

        jTabbedPane4.addTab("Mis suscripciones", jPanel3);

        Elementos.add(jTabbedPane4);
        jTabbedPane4.setBounds(10, 70, 920, 460);

        txtStateUp.setText("Uploads ok.");
        Elementos.add(txtStateUp);
        txtStateUp.setBounds(10, 530, 430, 30);

        txtStateDown.setText("Downloads ok.");
        Elementos.add(txtStateDown);
        txtStateDown.setBounds(450, 530, 480, 30);

        Principal.add(Elementos, "card2");
        Elementos.getAccessibleContext().setAccessibleDescription("");

        jMenu2.setText("Sesión");

        jMenuItem2.setText("Cerrar sesión");
        jMenuItem2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem2ActionPerformed(evt);
            }
        });
        jMenu2.add(jMenuItem2);

        jMenuBar1.add(jMenu2);

        setJMenuBar(jMenuBar1);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(Principal, javax.swing.GroupLayout.DEFAULT_SIZE, 943, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(Principal, javax.swing.GroupLayout.DEFAULT_SIZE, 568, Short.MAX_VALUE)
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    public void filesCatalogs(Catalog catalog, JList<String> list) {
        File directorio = new File(catalog.getPath());
        String[] arrArchivos = directorio.list();
        System.out.println(arrArchivos);
        DefaultListModel<String> model = new DefaultListModel<String>();
        if (arrArchivos != null) {
            Arrays.stream(arrArchivos).forEach(model::addElement);
            list.setModel(model);
        }
    }

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        try {
            // TODO add your handling code here:

            String name = txtCatalogSelected.getText();
            Catalog catalog = selected;
            System.out.println("\n\n" + catalog + "\n\n");
            //Runtime.getRuntime().exec("labelImg " + catalog.getPath());
            String cmd = "";
            switch (UtilOS.getOS()) {
                case WINDOWS:
                    cmd = ".\\labelImg.exe " + catalog.getPath();
                    break;
                case LINUX:
                    cmd = "python labelImg " + catalog.getPath();
            }
            Runtime.getRuntime().exec("labelImg \"" + catalog.getPath()+"\"");
            /*ProcessBuilder pb = new ProcessBuilder(cmd);
            Process p = pb.start();*/
        } catch (IOException ex) {
            ex.printStackTrace();
        }
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jMenuItem2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem2ActionPerformed
        // TODO add your handling code here:
        try {
            // TODO add your handling code here:
            DBManagement dbmanager = new DBManagement();
            dbmanager.closeSession(this.objUser);
            dbmanager.closeDB();
            this.donwloadExecutor.shutdown();
            this.uploadExecutor.shutdown();
            this.setVisible(false);
            AppAuth auth = new AppAuth();
            auth.setVisible(true);
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }//GEN-LAST:event_jMenuItem2ActionPerformed

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        JFileChooser chooser = new JFileChooser();
        chooser.setDialogTitle("SELECCIONA DONDE QUIERES QUE SE GUARDE EL CATÁLOGO");
        chooser.setCurrentDirectory(new File(this.workdir + "/" + this.objUser.getUsername()));

        int retrieval = chooser.showSaveDialog(null);
        if (retrieval == JFileChooser.APPROVE_OPTION) {
            try {
                File dir = new File(chooser.getSelectedFile().toString());
                dir.mkdir();

            } catch (Exception ex) {
                ex.printStackTrace();
            }
        }


    }//GEN-LAST:event_jButton2ActionPerformed

    private void catalogsTreeMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_catalogsTreeMouseClicked
        // TODO add your handling code here:
        
        try{
            int selRow = this.catalogsTree.getRowForLocation(evt.getX(), evt.getY());
            TreePath selPath = catalogsTree.getPathForLocation(evt.getX(), evt.getY());
            System.out.println(selPath.getLastPathComponent());
            String name = selPath.getLastPathComponent().toString();
            System.out.println(name);
            Catalog catalog = this.manager.getPublications().get(name);
            selected = catalog;
            filesCatalogs(catalog, this.list_files);
            this.txtCatalogSelected.setText(name.toUpperCase());
           
        }catch(NullPointerException ex){
            System.out.println("Data not selected");
        }
        
    }//GEN-LAST:event_catalogsTreeMouseClicked

    private void treeSubsMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_treeSubsMouseClicked
        // TODO add your handling code here:
        try{
            int selRow = this.treeSubs.getRowForLocation(evt.getX(), evt.getY());
            TreePath selPath = treeSubs.getPathForLocation(evt.getX(), evt.getY());
            System.out.println(selPath.getLastPathComponent());
            String name = selPath.getLastPathComponent().toString();
            System.out.println(name);
            Catalog catalog = this.manager.getSubscriptions().get(name);
            selected = catalog;
            filesCatalogs(catalog, this.list_subscriptions);
            this.txtSubSelected.setText(name.toUpperCase());
           
        }catch(NullPointerException ex){
            System.out.println("Data not selected");
        }
    }//GEN-LAST:event_treeSubsMouseClicked

    public void verificarCarpeta() throws FileNotFoundException, IOException {

        File verificar = new File("PATH.txt");

        if (verificar.exists()) {
            File crear;
            this.workdir = UtilFile.read(verificar);

            System.out.println("Ruta -> " + this.workdir);

            crear = new File(this.workdir);

            if (crear.exists()) {
                Principal.removeAll();
                Principal.add(Elementos);
                Principal.repaint();
                Principal.revalidate();
            } else {
                crear.mkdirs();
                Principal.removeAll();
                Principal.add(Elementos);
                Principal.repaint();
                Principal.revalidate();
                System.out.println("se creo");
            }
        } else {
            JOptionPane.showMessageDialog(this, "No cuenta con un directorio predeterminado.\nPor favor, eliga uno...", "Advertencia", 1);
            elegir.setFileSelectionMode(JFileChooser.DIRECTORIES_ONLY); //Seleccionar el directorio de cargas y descargas.

            //Se muestra la ventana para seleccionar el directorio.
            Principal.removeAll();
            Principal.add(elegir);
            Principal.repaint();
            Principal.revalidate();

            int select = elegir.showDialog(null, "Abrir");

            File sel = elegir.getSelectedFile();
            //Si el usuario, pincha en aceptar.
            if (select == JFileChooser.APPROVE_OPTION) {
                path = sel.getPath();
                System.out.println("ruta---" + path);
                {
                    try {
                        workdir = path + "/SkyCDS";
                        UtilFile.write("PATH.txt", workdir, false);
                        File skyCDS = new File(workdir);
                        skyCDS.mkdirs();
                    } catch (IOException ex) {
                        Logger.getLogger(App.class.getName()).log(Level.SEVERE, null, ex);
                    }
                }
            }
            Principal.removeAll();
            Principal.add(Elementos);
            Principal.repaint();
            Principal.revalidate();

        }
    }

    public void getApi() {

        inicioU_txt.setText(objUser.getUsername());
        System.out.println(objUser.getOrg().getAcronym());
        org_txt.setText(objUser.getOrg().getAcronym());

        File myFolder = new File(workdir + "/" + objUser.getUsername());
        System.out.println(myFolder.getAbsolutePath());
        if (!myFolder.exists()) {
            boolean b = myFolder.mkdir();
            System.out.println("Se creo myfolder " + b);
        } else {
            System.out.println("Ya existe");
        }
        startDownload();
        startUpload();

    }

    private void createTree(TreeMap<String, Catalog> cats, DefaultMutableTreeNode parent, boolean root, Set<String> added, String tokenParent) {
        //System.out.println(cats.size());
        //System.out.println(cats.size() + "\t" + tokenParent);
        for (Entry<String, Catalog> x : cats.entrySet()) {
            //System.out.println(tokenParent + "\t" + root + "\t" + x.getValue().getFather() + "\t" + x.getValue().getName());

            if (x.getValue().getFather().equals(tokenParent) && root) {
                DefaultMutableTreeNode catalog = new DefaultMutableTreeNode(x.getValue().getName());
                parent.add(catalog);
                added.add(x.getValue().getToken());
                
                /*for (Entry<String, Catalog> y : x.getValue().getCatalogs().entrySet()){
                   System.out.println(x.getValue().getName() + "\t" + y.getValue().getName() + "\t" + y.getValue().getFather() + "\t" + x.getValue().getToken());
                }*/
                
                createTree(x.getValue().getCatalogs(), catalog, false, added, x.getValue().getToken());
            } else if (x.getValue().getFather().equals(tokenParent) && !root) {
                DefaultMutableTreeNode catalog = new DefaultMutableTreeNode(x.getValue().getName());
                parent.add(catalog);
                added.add(x.getValue().getToken());
                
                /*for (Entry<String, Catalog> y : x.getValue().getCatalogs().entrySet()){
                    System.out.println(x.getValue().getName() + "\t" + y.getValue().getName() + "\t" + y.getValue().getFather() + "\t" + x.getValue().getToken());
                }*/
                
                createTree(x.getValue().getCatalogs(), catalog, false, added, x.getValue().getToken());
            }
            
        }
        /*cats.entrySet().forEach(x -> {
            System.out.println(x.getValue().getFather() + "\t" + x.getValue().getName() + "\t" + x.getValue().getCatalogs().size());
            if (!added.contains(added.add(x.getValue().getToken()))) {
                if (x.getValue().getFather().equals("/") && root) {
                    DefaultMutableTreeNode catalog = new DefaultMutableTreeNode(x.getValue().getName());
                    parent.add(catalog);
                    added.add(x.getValue().getToken());
                    createTree(x.getValue().getCatalogs(), catalog, false, added);
                } else {
                    DefaultMutableTreeNode catalog = new DefaultMutableTreeNode(x.getValue().getName());
                    parent.add(catalog);
                    added.add(x.getValue().getToken());
                    createTree(x.getValue().getCatalogs(), catalog, false, added);
                }
            }

        });*/
    }
    
    private Set<String> findRoots(TreeMap<String, Catalog> cats){
        Set<String> roots = new HashSet<>();
        Set<String> tokens = new HashSet<>();
        roots.add("/");
        
        for (Entry<String, Catalog> x : cats.entrySet()) {
            tokens.add(x.getValue().getToken());
        }
        
        for (Entry<String, Catalog> x : cats.entrySet()) {
            if(!tokens.contains(x.getValue().getFather())){
                roots.add(x.getValue().getFather());
            }
        }
        System.out.println(roots);
        return roots;
    }

    private void getResources() {
        System.out.println("Getting catalogs...");
        App.txtStateDown.setText("Obteniendo información del servidor...");

        try {
            TreeMap<String, Catalog> pubs = this.manager.getPublicationsFromServer();
            TreeMap<String, Catalog> subs = this.manager.getSubscriptionsFromServer();
            TreeMap<String, Catalog> subsFinal = new TreeMap<>();
            //TreeMap<String, Catalog> pubsOri = new TreeMap<>();
            DefaultListModel<String> subsModel = new DefaultListModel<>();
            //pubsOri.putAll(pubs);


            //this.manager.setPublications(pubsOri);

            subs.entrySet().forEach(x -> {
                if (!pubs.containsKey(x.getKey())) {
                    subsFinal.put(x.getKey(), x.getValue());
                }

            });

            DefaultMutableTreeNode publicationsNode = new DefaultMutableTreeNode("Publications");
            DefaultMutableTreeNode subsNode = new DefaultMutableTreeNode("Subscriptions");
            
            Set<String> pubRoots = this.findRoots(pubs);
            Set<String> subRoots = this.findRoots(subsFinal);
            
            for(String root:pubRoots){
                createTree(pubs, publicationsNode, true, new HashSet<>(), root);
            }
            
            for(String root:subRoots){
                createTree(subsFinal, subsNode, true, new HashSet<>(), root);
            }
            
            
            DefaultTreeModel treeModel = new DefaultTreeModel(publicationsNode);
            this.catalogsTree.setModel(treeModel);
            
            DefaultTreeModel treeModelSubs = new DefaultTreeModel(subsNode);
            this.treeSubs.setModel(treeModelSubs);

            //Arrays.stream(arrArchivos).forEach(model::addElement);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private List<String> listFiles(String path) throws IOException {
        System.out.println("\n\n\n" + path + "\n\n\n");
        List<String> files = new ArrayList<>();
        try ( Stream<Path> paths = Files.walk(Paths.get(path))) {
            paths
                    .filter(Files::isRegularFile)
                    .forEach(f -> {
                        files.add(f.toString());
                    });
        }
        return files;
    }

    private void startDownload() {
        /*download = new Thread() {
            @Override
            public void run() {
                while (running) {

                    try {
                        getResources();
                        Thread.sleep(10000);
                    } catch (InterruptedException ex) {
                        Logger.getLogger(App.class.getName()).log(Level.SEVERE, null, ex);
                    }
                }
            }
        };
        download.start();*/
    }

    private void startUpload() {
        Api api = new Api();
        /*upload = new Thread() {
            @Override
            public void run() {
                while (running) {
                    try {
                        //upload(api);
                        Thread.sleep(10000);
                    } catch (InterruptedException ex) {
                        Logger.getLogger(App.class.getName()).log(Level.SEVERE, null, ex);
                    } catch (IOException ex) {
                        Logger.getLogger(App.class.getName()).log(Level.SEVERE, null, ex);
                    } catch (JSONException ex) {
                        Logger.getLogger(App.class.getName()).log(Level.SEVERE, null, ex);
                    }
                }
            }
        };*/
        //upload.start();
    }


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JPanel Elementos;
    private javax.swing.JPanel Organizacion;
    private javax.swing.JPanel Principal;
    private javax.swing.JLabel archivos_lb;
    private javax.swing.JLabel archivos_lb1;
    private javax.swing.JLabel catalogos;
    private javax.swing.JLabel catalogos1;
    private javax.swing.JTree catalogsTree;
    private javax.swing.JLabel inicioU_txt;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton2;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JMenu jMenu2;
    private javax.swing.JMenuBar jMenuBar1;
    private javax.swing.JMenuItem jMenuItem1;
    private javax.swing.JMenuItem jMenuItem2;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTabbedPane jTabbedPane4;
    private javax.swing.JList<String> list_files;
    private javax.swing.JList<String> list_subscriptions;
    private javax.swing.JLabel logoCarpeta;
    private javax.swing.JLabel logoCarpeta1;
    private javax.swing.JLabel org_txt;
    private javax.swing.JScrollPane panelArchivos1;
    private javax.swing.JScrollPane panelArchivos2;
    private javax.swing.JLabel sesion_lb;
    private javax.swing.JTree treeSubs;
    private javax.swing.JLabel txtCatalogSelected;
    public static javax.swing.JLabel txtStateDown;
    public static javax.swing.JLabel txtStateUp;
    private javax.swing.JLabel txtSubSelected;
    // End of variables declaration//GEN-END:variables

}
