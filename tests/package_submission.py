"""
TaskFlow – Submission Package Generator
Packages all project files, database schema, tests, documentation, and screenshots
into a clean, verified TaskFlow_RTIH_Submission.zip archive.
"""

import os
import zipfile
import sys

def package_submission():
    zip_filename = "TaskFlow_RTIH_Submission.zip"
    base_dir = os.path.abspath(".")
    
    # Exclude list
    exclude_dirs = {".git", "__pycache__", ".vscode", "chrome_profile_capture", "chrome_debug_profile"}
    exclude_files = {zip_filename, ".DS_Store", "Thumbs.db"}
    
    print(f"Creating submission package: {zip_filename}...")
    
    file_count = 0
    total_uncompressed_bytes = 0
    
    with zipfile.ZipFile(zip_filename, "w", zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(base_dir):
            # Prune excluded directories
            dirs[:] = [d for d in dirs if d not in exclude_dirs]
            
            for f in files:
                if f in exclude_files or f.endswith(".pyc") or f.endswith(".tmp"):
                    continue
                
                full_path = os.path.join(root, f)
                rel_path = os.path.relpath(full_path, base_dir)
                
                # Archive relative to project root
                archive_name = rel_path.replace("\\", "/")
                
                file_size = os.path.getsize(full_path)
                total_uncompressed_bytes += file_size
                file_count += 1
                
                zipf.write(full_path, archive_name)
                print(f"  + Added: {archive_name} ({file_size:,} bytes)")
                
    zip_size = os.path.getsize(zip_filename)
    print("\n==================================================")
    print("SUBMISSION PACKAGE GENERATED SUCCESSFULLY")
    print("==================================================")
    print(f"Archive:               {zip_filename}")
    print(f"Total Files Archived:  {file_count}")
    print(f"Uncompressed Size:     {total_uncompressed_bytes:,} bytes")
    print(f"Compressed ZIP Size:   {zip_size:,} bytes")
    print("==================================================")

if __name__ == "__main__":
    package_submission()

