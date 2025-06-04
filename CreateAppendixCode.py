import os
import sys

def extract_files_content(root_directory, output_file):
    """
    Perform depth-first search through directory and extract content from
    .php, .js, .html, and .css files into a single text file.
    
    Args:
        root_directory (str): Path to the root directory to search
        output_file (str): Path to the output text file
    """
    target_extensions = {'.php', '.js', '.html', '.css', '.sql'}
    
    try:
        with open(output_file, 'w', encoding='utf-8') as outfile:
            for root, dirs, files in os.walk(root_directory):
                for file in files:
                    # Check if file has target extension
                    _, ext = os.path.splitext(file)
                    if ext.lower() in target_extensions:
                        file_path = os.path.join(root, file)
                        
                        try:
                            # Write filename header
                            outfile.write(f"{file}\n\n")
                            
                            # Read and write file content
                            with open(file_path, 'r', encoding='utf-8', errors='ignore') as infile:
                                content = infile.read()
                                outfile.write(content)
                            
                            # Add separator between files
                            outfile.write("\n\n" + "="*50 + "\n\n")
                            
                            print(f"Processed: {file_path}")
                            
                        except Exception as e:
                            print(f"Error reading {file_path}: {e}")
                            outfile.write(f"[Error reading file: {e}]\n\n")
                            outfile.write("="*50 + "\n\n")
    
    except Exception as e:
        print(f"Error writing to output file: {e}")
        return False
    
    return True

def main():
    # Get directory and output file from command line arguments or use defaults
    if len(sys.argv) >= 2:
        root_dir = sys.argv[1]
    else:
        root_dir = input("Enter the directory path to search: ").strip()
    
    if len(sys.argv) >= 3:
        output_file = sys.argv[2]
    else:
        output_file = input("Enter output file name (default: extracted_files.txt): ").strip()
        if not output_file:
            output_file = "extracted_files.txt"
    
    # Validate directory exists
    if not os.path.isdir(root_dir):
        print(f"Error: Directory '{root_dir}' does not exist.")
        return
    
    print(f"Searching directory: {root_dir}")
    print(f"Output file: {output_file}")
    print("Target extensions: .php, .js, .html, .css")
    print("-" * 40)
    
    success = extract_files_content(root_dir, output_file)
    
    if success:
        print(f"\nExtraction completed successfully!")
        print(f"Results saved to: {output_file}")
    else:
        print("\nExtraction failed.")

if __name__ == "__main__":
    main()