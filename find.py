import os

def find_in_subdirs(base_dir, target_name):
    """
    Recursively search for a specific file or directory within all subdirectories of a base directory.

    :param base_dir: The base directory to start the search from.
    :param target_name: The name of the file or directory to search for.
    :return: A list of full paths where the target is found.
    """
    matches = []
    for root, dirs, files in os.walk(base_dir):
        # Check in directories
        for directory in dirs:
            if directory == target_name:
                matches.append(os.path.join(root, directory))
        
        # Check in files
        for file in files:
            if file == target_name:
                matches.append(os.path.join(root, file))
    
    return matches

# Example usage
if __name__ == "__main__":
    base_directory = input("Enter the base directory to search in: ").strip()
    target_name = input("Enter the name of the file or directory to find: ").strip()

    if not os.path.exists(base_directory):
        print(f"Error: Base directory '{base_directory}' does not exist.")
    else:
        results = find_in_subdirs(base_directory, target_name)
        if results:
            print("Found the following matches:")
            for result in results:
                print(result)
        else:
            print(f"No matches found for '{target_name}' in '{base_directory}'.")
