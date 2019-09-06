#!/usr/bin/env python3

import os
import shutil
from datetime import datetime
from os import listdir
from os.path import isfile, join

_MIGRATION_PATH = "database/migrations/"
_MODELS_PATH = "application/models/"
_STORAGE_PATH = "application/storage/"


def cap(x):
    return x.replace("TABLE ", "").strip().capitalize().rstrip('s')


def low(x):
    return x.replace("TABLE ", "").strip().lower()


def menu():
    print("\tMENU\n---------------------------")
    print(
        "\na) Create migration file\nb) Sync database\nc) Create model from migration\nd) Clean storage (cache/logs/seessions)\nq) Exit")
    return input("Choose one: ")


def privilege(model):
    f = open(_MODELS_PATH + model + ".php", "a")
    f.write("\tconst PRIV_ADMINISTRATOR = 1;\n")
    f.write("\tconst PRIV_MODERATOR= 2;\n")
    f.write("\tconst PRIV_EDITOR= 3;\n")
    f.write("\tconst PRIV_MEMBER= 8;\n")
    f.write("\tconst PRIV_GUEST= 99;\n\n")
    f.close()


def params(x, model):
    f = open(_MODELS_PATH + model + ".php", "a")
    for i in x:
        param = i.split(":")
        attribute = param[0].strip()
        if attribute[0] == "+":
            f.write("\tpublic $" + attribute.lstrip("+") + ";\n")
    f.close()


def getters(x, model):
    f = open(_MODELS_PATH + model + ".php", "a")
    for i in x:
        param = i.split(":")
        attribute = param[0].strip()
        if attribute[0] == "+":
            f.write("\n\tpublic function get" + attribute.lstrip("+").capitalize() + "()")
            f.write("\n\t{\n")
            f.write('\n\t\treturn $this->get("' + attribute.lstrip("+") + '");')
            f.write("\n\t}\n")
    f.close()


def setters(x, model):
    f = open(_MODELS_PATH + model + ".php", "a")
    for i in x:
        param = i.split(":")
        attribute = param[0].strip()
        if attribute[0] == "+":
            f.write("\n\tpublic function set" + attribute.lstrip("+").capitalize() + "($value)")
            f.write("\n\t{\n")
            f.write('\n\t\t$this->set("' + attribute.lstrip("+") + '", $value);')
            f.write("\n\t}\n")
    f.close()


def where(x, model):
    f = open(_MODELS_PATH + model + ".php", "a")
    for i in x:
        param = i.split(":")
        attribute = param[0].strip()
        if attribute[0] == "+":
            f.write("\n\tpublic function where" + attribute.lstrip("+").capitalize() + "($" + attribute.lstrip("+") + ")")
            f.write("\n\t{\n")
            f.write('\n\t\treturn $this->db->select(" * FROM " . $this->_table . " WHERE ' + attribute.lstrip("+") + ' = :' + attribute.lstrip("+") + '", [":' + attribute.lstrip("+") + '" => $' + attribute.lstrip("+") + ']);')
            f.write("\n\t}\n")
    f.close()


def main():
    while True:
        option = menu();
        if option.lower() == "q":
            break
        elif option.lower() == "a":
            table = input("Name of the table:")
            time = datetime.now().strftime('%Y%m%d%H%M%S')
            f = open(_MIGRATION_PATH + time + "_" + table + ".migration", "w+")
            f.write("TABLE " + table.upper())
            f.close()
            print("New migration file created at: " + _MIGRATION_PATH + time + "_" + table + ".migration")
        elif option.lower() == "c":
            tables = []
            values = []
            files = [f for f in listdir(_MIGRATION_PATH) if isfile(join(_MIGRATION_PATH, f))]
            for file in files:
                with open(join(_MIGRATION_PATH, file), 'r') as f:
                    table = f.readline();
                    tables.append(table)
                    lines = f.read().splitlines()
                    values.append([table, lines])
            for x in values:
                tab = ""
                par = []
                for i in x:
                    if isinstance(i, list):
                        par = i
                    else:
                        tab = i
                model = cap(tab)
                table = low(tab)
                f = open(_MODELS_PATH + model + ".php", "w+")
                f.write("<?php\n\nnamespace application\\models;\n\n\nuse application\\core\\Model;\n\nclass " + model + " extends Model\n{\n")
                f.write("\tprotected static $_table = '" + table + "';\n\tprotected static $primaryKey = 'id';\n\n")
                f.close()

                if model.lower() == "user":
                    privilege(model)
                params(par, model)
                getters(par, model)
                setters(par, model)
                where(par, model)

                f = open(_MODELS_PATH + model + ".php", "a")
                f.write("\n}")
                f.close()
        elif option.lower() == "d":
            folders = [_STORAGE_PATH + 'cache', _STORAGE_PATH + 'sessions', _STORAGE_PATH + 'logs']
            for folder in folders:
                for the_file in os.listdir(folder):
                    file_path = os.path.join(folder, the_file)
                    try:
                        if os.path.isfile(file_path):
                            os.unlink(file_path)
                        elif os.path.isdir(file_path):
                            shutil.rmtree(file_path)
                    except Exception as e:
                        print(e)
                print(folder + " cleared")


main()