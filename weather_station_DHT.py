#! /usr/bin/python
# -*- coding: cp1252 -*-

import Adafruit_DHT as dht
import time
import os
import MySQLdb

def connect_database():
    con = MySQLdb.connect(host = "192.168.0.112", user = "testaaja", passwd = "salasana", db = "dht")
    return con

def read_parameter(curs, parameter_name):
    sql_query = "SELECT value FROM parameters WHERE name = %s"
    
    success = False
    value = "0"

    try:      
        curs.execute (sql_query, (parameter_name))
        results = curs.fetchall()
        for row in results:
            value = row[0]
        success = True
    except:
        print( "Virhe: parametria ei voitu lukea")

    return success, value

def main():
    
    DEFAULT_INTERVAL = 900    #15 minuuttia
    GPIO_PIN = 23

    enabled = 1
    interval = DEFAULT_INTERVAL
    
    
    while True:
        
        con = connect_database();
        curs=con.cursor()
        
        success, enabled_ = read_parameter(curs, "enabled")
        if success:
            enabled = int(enabled_)
        else:
            enabled = 1
            print( "Virhe: parametrin luku ei onnistunut (2)")

        print("ulompi looppi: enabled %s" % (enabled)) #testitulostusta
        if enabled == 1:
            success, interval_ = read_parameter(curs, "interval")
            if success:
                interval = int(interval_)
            else:
                interval = DEFAULT_INTERVAL
                print( "Virhe: parametrin luku ei onnistunut (3)")
        print("ulompi: interval %s" % (interval)) #testitulostusta
        
        while enabled == 1:
            humidity, temperature = dht.read_retry(dht.DHT22, GPIO_PIN)
            temperature = round(temperature, 1)
            humidity = round(humidity, 1)    
    
            date_ = (os.popen('date +%Y-%m-%d').read())
            time_ =  (os.popen('date +%T').read())

            # poistetaan rivinvaihdot
            date_  = date_[:-1] 
            time_  = time_[:-1]

            # testitulostusta:
           
            print("\ndate %s" % date_)
            print("time %s" % time_)
            print("temperature %s" % temperature)
            print("humidity %s" % humidity)

            # muodostetaan yhteys LAMP-serverin tietokantaan
            con = connect_database()
            curs = con.cursor()

            sql_query = "INSERT INTO data_table (date, time, temperature, humidity) VALUES (%s, %s, %s, %s)"
            
             # tietokantaan talletus
            try:
                curs.execute(sql_query, (date_, time_, temperature, humidity))
                con.commit()
                print("Data talletettu tietokantaan")

            except:
                print( "Virhe: dataa ei talletettu tietokantaan")
                con.rollback()

            # luetaan parametrit (mittausjakso: "interval", onko mittaus päällä: "enabled")
            success, interval_ = read_parameter(curs, "interval")
            if success:
                interval = int(interval_)
            else:
                interval = DEFAULT_INTERVAL
            
            success, enabled_ = read_parameter(curs, "enabled")
            if success:
                enabled = int(enabled_)
            else:
                enabled = 1

            print("success: %s interval: %s" % (success, interval))
            print("success: %s enabled: %s" % (success, enabled))
        
            counter = 0
            read_counter = 0
            while counter < interval and enabled == 1:
                time.sleep(1)
                counter += 1
                read_counter += 1

                if read_counter > 10:
                    con = connect_database()
                    curs=con.cursor()
                    success, enabled_ = read_parameter(curs, "enabled")
                    if success:
                        enabled = int(enabled_)
                    else:
                        enabled = 1
                        print( "Virhe: parametrin luku ei onnistunut (1)")
                    read_counter = 0
                    print("sisempi looppi: enabled %s" % (enabled)) #testitulostust
        
        time.sleep(10) #odotetaan 10 s ennen kuin seuraavan kerran tarkistetaan onko mittaus päällä

if __name__ == "__main__":
    main()
