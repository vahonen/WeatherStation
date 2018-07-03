#! /usr/bin/python
# -*- coding: cp1252 -*-

import Adafruit_DHT as dht
import time
import os
import MySQLdb
#import serial

sense = SenseHat()

while True:
    humidity,temperature = dht.read_retry(dht.DHT22, 23)
    print 'Temp={0:0.1f}*C Humidity={1:0.1f}%'.format(t, h)

    temperature = round(temperature, 1)
    humidity = round(humidity, 1)
	pressure = 0

    # muodostetaan yhteys LAMP-serverin tietokantaan
    db = MySQLdb.connect(host = "192.168.0.112", user = "testaaja", passwd = "salasana", db = "sense_data")
    curs=db.cursor()
    insert_stmt = (
      "INSERT INTO data_table (date, time, temperature, humidity, pressure) "
      "VALUES (%s, %s, %s, %s, %s)"
    )

    date_ = (os.popen('date +%Y-%m-%d').read())
    time_ =  (os.popen('date +%T').read())

    date_  = date_[:-1] # poistetaan rivinvaihdot
    time_  = time_[:-1]

    print("date",date_)
    print("time",time_)
    print("temperature",temperature)
    print("humidity", humidity)
    print("pressure",pressure)
	
	

    try:
    	    curs.execute (insert_stmt, (date_, time_, temperature, humidity, pressure))
    	    db.commit()
    	    print("Data committed")

    except:
    	    print( "Error: the database is being rolled back")
    	    db.rollback()

    #msg = "T = %s P = %s H = %s" % (temperature, pressure, humidity)
    #sense.show_message(msg)

    time.sleep(10)
	


    
    
