import mysql.connector
from graphviz import Digraph

# Configuration
DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",  # Empty password
    "database": "reviewsync"
}

def get_all_tables(cursor):
    """
    Retrieve all table names in the database.
    """
    cursor.execute("SHOW TABLES")
    return [table[0] for table in cursor.fetchall()]

def get_table_schema(cursor, table_name):
    """
    Retrieve the schema of a specific table.
    """
    query = f"DESCRIBE {table_name}"
    cursor.execute(query)
    return cursor.fetchall()

def get_foreign_keys(cursor, table_name):
    """
    Retrieve foreign key relationships for a table.
    """
    query = f"""
    SELECT 
        TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM 
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE 
        TABLE_SCHEMA = '{DB_CONFIG['database']}'
        AND TABLE_NAME = '{table_name}'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    """
    cursor.execute(query)
    return cursor.fetchall()

def create_diagram(tables, schemas, relationships):
    """
    Generate a diagram for the entire database.
    """
    diagram = Digraph("Database Schema", format="png")
    diagram.attr(rankdir="TB", splines="ortho")

    # Add tables and fields
    for table, schema in schemas.items():
        fields = "\n".join(
            [
                f"{field[0]} ({field[1]})" +
                (" [PK]" if field[3] == "PRI" else "") +
                (" [AI]" if "auto_increment" in field[5].lower() else "")
                for field in schema
            ]
        )
        diagram.node(
            table,
            label=f"<<TABLE BORDER='0' CELLBORDER='1' CELLSPACING='0'>"
                  f"<TR><TD BGCOLOR='lightblue'><B>{table}</B></TD></TR>"
                  + "".join(f"<TR><TD ALIGN='LEFT'>{field}</TD></TR>" for field in fields.split("\n"))
                  + "</TABLE>>",
            shape="plain"
        )

    # Add relationships
    for relationship in relationships:
        table, column, ref_table, ref_column = relationship
        diagram.edge(f"{table}:{column}", f"{ref_table}:{ref_column}", arrowhead="vee")

    return diagram

def main():
    # Connect to the MySQL database
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()

        # Get all tables
        tables = get_all_tables(cursor)

        # Retrieve schemas and relationships
        schemas = {table: get_table_schema(cursor, table) for table in tables}
        relationships = []
        for table in tables:
            relationships.extend(get_foreign_keys(cursor, table))

        # Generate and save diagram
        diagram = create_diagram(tables, schemas, relationships)
        diagram.render("reviewsync_schema", cleanup=True)
        print("Diagram saved as reviewsync_schema.png")

    except mysql.connector.Error as err:
        print(f"Error: {err}")

    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

if __name__ == "__main__":
    main()
