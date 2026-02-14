#!/usr/bin/env python3
import tkinter as tk
from tkinter import filedialog, messagebox
import subprocess
import os
import signal
import threading
import time
import psutil
import sys

class MaximaOnlineGUI:
    def __init__(self, root):
        self.root = root
        self.root.title("Maxima Online")
        self.root.geometry("700x380")
        
        self.yamwi_dir = tk.StringVar()
        self.php_process = None
        self.php_pid = None
        
        self.create_widgets()
    
    def create_widgets(self):
        title = tk.Label(self.root, text="Maxima Online", font=("Arial", 18, "bold"))
        title.pack(pady=15)
        
        path_frame = tk.Frame(self.root)
        path_frame.pack(pady=5, padx=25, fill="x")
        
        tk.Label(path_frame, text="Directory of Yamwi:", font=("Arial", 11)).pack(anchor="w")
        path_entry = tk.Entry(path_frame, textvariable=self.yamwi_dir, width=60, font=("Arial", 10))
        path_entry.pack(fill="x", pady=(5,0))
        
        tk.Button(path_frame, text="Choose the directory", 
                 command=self.choose_directory, font=("Arial", 11)).pack(pady=5)
        
        btn_frame = tk.Frame(self.root)
        btn_frame.pack(pady=15)
        
        # Button 1: Launch the software
        self.start_btn = tk.Button(btn_frame, text="🟢 Launch Maxima Online",
            command=self.start_server, bg="#4CAF50", fg="white",
            activebackground="#4CAF50", activeforeground="white",
            disabledforeground="#BDBDBD", relief="raised", bd=3,
            font=("Arial", 12, "bold"), width=22, height=1)
        self.start_btn.pack(pady=6)
        
        # Button 2: Stop the software
        self.stop_btn = tk.Button(btn_frame, text="🔴 Shutdown Maxima Online", 
            command=self.stop_server, bg="#f44336", fg="white",
            activebackground="#f44336", activeforeground="white",
            disabledforeground="#BDBDBD", relief="raised", bd=3,
            font=("Arial", 12, "bold"), width=22, height=1, state="disabled")
        self.stop_btn.pack(pady=6)
        
        # Button 3: Quit the program
        self.quit_btn = tk.Button(btn_frame, text="🟠 Quit", 
            command=self.quit_app, bg="#FF9800", fg="white",
            activebackground="#FF9800", activeforeground="white",
            relief="raised", bd=3, font=("Arial", 12, "bold"),
            width=22, height=1)
        self.quit_btn.pack(pady=6)
        
        # Status at the bottom
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
                messagebox.showerror("Error", "Hidden folders are not allowed.")
                return
            
            # ✅ VERIFICATION: Does the directory contain index.php or any PHP file?
            php_files = [f for f in os.listdir(directory) if f.endswith('.php')]
            if not php_files:
                response = messagebox.askokcancel("Warning", 
                    f"No PHP files found in {os.path.basename(directory)}.\nDo you want to continue anyway?")
                if not response:
                    return
            
            self.yamwi_dir.set(directory)
            self.status_label.config(text=f"✅ Selected directory: {os.path.basename(directory)}", fg="green")
    
    def start_server(self):
        if not self.yamwi_dir.get():
            messagebox.showerror("Error", "Please first choose the Yamwi directory")
            return
        
        if self.is_port_in_use(8080):
            messagebox.showwarning("Warning", "Port 8080 is already in use")
            return
        
        try:
            # ✅ CRITICAL FIX: Use cwd parameter instead of os.chdir()
            # This ensures PHP starts in the correct directory even after compilation
            working_dir = self.yamwi_dir.get()
            
            # Check that the directory still exists
            if not os.path.exists(working_dir):
                messagebox.showerror("Error", f"Directory no longer exists: {working_dir}")
                return
            
            # ✅ Use cwd parameter of Popen instead of os.chdir()
            self.php_process = subprocess.Popen(
                ["php", "-S", "localhost:8080"],
                cwd=working_dir,  # ← CRITICAL FIX
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                stdin=subprocess.DEVNULL
            )
            self.php_pid = self.php_process.pid
            
            # ✅ Wait a bit longer for the server to really start
            def open_browser():
                time.sleep(2)  # Increased from 1.5 to 2 seconds
                
                # Check that the server is still running
                if self.php_process and self.php_process.poll() is None:
                    subprocess.run(["xdg-open", "http://localhost:8080"], 
                                 stdout=subprocess.DEVNULL, 
                                 stderr=subprocess.DEVNULL)
                else:
                    self.root.after(0, lambda: messagebox.showerror("Error", 
                        "PHP server stopped immediately. Check that PHP is installed."))
                    self.root.after(0, self.stop_server)
            
            threading.Thread(target=open_browser, daemon=True).start()
            
            self.start_btn.config(state="disabled")
            self.stop_btn.config(state="normal")
            self.status_label.config(text="🚀 Server started - localhost:8080", fg="green")
            
        except FileNotFoundError:
            messagebox.showerror("Error", "PHP is not installed or not in PATH")
        except Exception as e:
            messagebox.showerror("Error", f"Unable to start the server: {str(e)}")
    
    def is_port_in_use(self, port):
        try:
            for conn in psutil.net_connections():
                if conn.laddr.port == port:
                    return True
        except:
            pass
        return False
    
    def stop_server(self):
        self.quit_php_process()
    
    def quit_php_process(self, show_message=False):
        if self.php_pid:
            try:
                process = psutil.Process(self.php_pid)
                process.terminate()
                process.wait(timeout=3)
                
                # Clean up other PHP processes on port 8080
                for proc in psutil.process_iter(['pid', 'name', 'cmdline']):
                    try:
                        if 'php' in proc.info['name'].lower() and proc.info['pid'] != self.php_pid:
                            cmdline = ' '.join(proc.info['cmdline'] or [])
                            if '8080' in cmdline:
                                proc.terminate()
                    except:
                        pass
                
            except psutil.NoSuchProcess:
                pass
            except Exception as e:
                pass
            
            self.php_process = None
            self.php_pid = None
            self.start_btn.config(state="normal")
            self.stop_btn.config(state="disabled")
            self.status_label.config(text="ℹ️ Server stopped correctly", fg="orange")
            
            if show_message:
                messagebox.showinfo("Info", "Server stopped")
    
    def quit_app(self):
        self.quit_php_process(show_message=False)
        self.root.quit()

if __name__ == "__main__":
    root = tk.Tk()
    app = MaximaOnlineGUI(root)
    root.mainloop()
