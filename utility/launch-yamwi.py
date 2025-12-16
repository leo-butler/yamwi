#!/usr/bin/env python3
import tkinter as tk
from tkinter import filedialog, messagebox
import subprocess
import os
import signal
import threading
import time
import psutil

class MaximaOnlineGUI:
    def __init__(self, root):
        self.root = root
        self.root.title("Maxima Online")
        self.root.geometry("700x380")  # Ajusté pour statut visible en bas
        
        self.yamwi_dir = tk.StringVar()
        self.php_process = None
        self.php_pid = None
        
        self.create_widgets()
    
    def create_widgets(self):
        title = tk.Label(self.root, text="Maxima Online", font=("Arial", 18, "bold"))
        title.pack(pady=15)
        
        path_frame = tk.Frame(self.root)
        path_frame.pack(pady=5, padx=25, fill="x")  # ✅ Moins d'espace
        
        tk.Label(path_frame, text="Directory of Yamwi:", font=("Arial", 11)).pack(anchor="w")
        path_entry = tk.Entry(path_frame, textvariable=self.yamwi_dir, width=60, font=("Arial", 10))
        path_entry.pack(fill="x", pady=(5,0))
        
        tk.Button(path_frame, text="Choose the directory", 
                 command=self.choose_directory, font=("Arial", 11)).pack(pady=5)
        
        btn_frame = tk.Frame(self.root)
        btn_frame.pack(pady=15)  # ✅ Espacement optimisé
        
        # Bouton 1: Lancer le logiciel
        self.start_btn = tk.Button(btn_frame, text="🟢 Launch Maxima Online",
            command=self.start_server, bg="#4CAF50", fg="white",
            activebackground="#4CAF50", activeforeground="white",
            disabledforeground="#BDBDBD", relief="raised", bd=3,
            font=("Arial", 12, "bold"), width=22, height=1)
        self.start_btn.pack(pady=6)
        
        # Bouton 2: Arrêter le logiciel
        self.stop_btn = tk.Button(btn_frame, text="🔴 Shutdown Maxima Online", 
            command=self.stop_server, bg="#f44336", fg="white",
            activebackground="#f44336", activeforeground="white",
            disabledforeground="#BDBDBD", relief="raised", bd=3,
            font=("Arial", 12, "bold"), width=22, height=1, state="disabled")
        self.stop_btn.pack(pady=6)
        
        # Bouton 3: Quitter le programme
        self.quit_btn = tk.Button(btn_frame, text="🟠 Quit", 
            command=self.quit_app, bg="#FF9800", fg="white",
            activebackground="#FF9800", activeforeground="white",
            relief="raised", bd=3, font=("Arial", 12, "bold"),
            width=22, height=1)
        self.quit_btn.pack(pady=6)
        
        # ✅ STATUT EN BAS - PROCHE DES BOUTONS ET VISIBLE
        status_frame = tk.Frame(self.root)
        status_frame.pack(side="bottom", pady=10, fill="x")
        
        self.status_label = tk.Label(status_frame, text="Ready to launch Maxima Online", 
                                   fg="blue", font=("Arial", 11, "bold"), 
                                   anchor="w", padx=25)
        self.status_label.pack()
    
    def choose_directory(self):
        directory = filedialog.askdirectory(title="Choose the directory for Maxima Online", mustexist=True, parent=self.root)
        if directory:
            base_folder = os.path.basename(directory)
            if base_folder.startswith('.'):
                messagebox.showerror("Error", "Les dossiers cachés ne sont pas autorisés.")
                return
            self.yamwi_dir.set(directory)
            self.status_label.config(text=f"✅ Selected directory sélectionné: {os.path.basename(directory)}", fg="green")
    
    def start_server(self):
        if not self.yamwi_dir.get():
            messagebox.showerror("Error", "Please first choose the Yamwi directory")
            return
        
        if self.is_port_in_use(8080):
            messagebox.showwarning("Warning", "The 8080 port is already used")
            return
        
        try:
            os.chdir(self.yamwi_dir.get())
            self.php_process = subprocess.Popen(["php", "-S", "localhost:8080"], 
                                              stdout=subprocess.DEVNULL, 
                                              stderr=subprocess.DEVNULL)
            self.php_pid = self.php_process.pid
            
            def open_browser():
                time.sleep(1.5)
                subprocess.run(["xdg-open", "http://localhost:8080"])
            
            threading.Thread(target=open_browser, daemon=True).start()
            
            self.start_btn.config(state="disabled")
            self.stop_btn.config(state="normal")
            self.status_label.config(text="🚀 Server started - localhost:8080", fg="green")
            
        except Exception as e:
            messagebox.showerror("Error", f"IUnable to start the server: {str(e)}")
    
    def is_port_in_use(self, port):
        for conn in psutil.net_connections():
            if conn.laddr.port == port:
                return True
        return False
    
    def stop_server(self):
        self.quit_php_process()
    
    def quit_php_process(self, show_message=False):
        if self.php_pid:
            try:
                process = psutil.Process(self.php_pid)
                process.terminate()
                process.wait(timeout=3)
                
                for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
                    if 'php' in proc.info['name'].lower() and proc.info['pid'] != self.php_pid:
                        cmdline = ' '.join(proc.info['cmdline'] or [])
                        if '8080' in cmdline:
                            proc.terminate()
                
            except psutil.NoSuchProcess:
                pass
            except:
                pass
            
            self.php_process = None
            self.php_pid = None
            self.start_btn.config(state="normal")
            self.stop_btn.config(state="disabled")
            self.status_label.config(text="⏹️ Server stopped correctly", fg="orange")
            
            if show_message:
                messagebox.showinfo("Info", "No server currently running")
    
    def quit_app(self):
        self.quit_php_process(show_message=False)
        self.root.quit()

if __name__ == "__main__":
    root = tk.Tk()
    app = MaximaOnlineGUI(root)
    root.mainloop()

