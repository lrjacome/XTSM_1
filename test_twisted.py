# -*- coding: utf-8 -*-
"""
Basic test of a twisted server, starting from an Echo Protocol, then building
a command server.

Created on Thu May 16 18:24:40 2013

@author: Nate
"""

from twisted.internet import protocol, reactor
import pdb
import mimetools
from BaseHTTPServer import BaseHTTPRequestHandler
from StringIO import StringIO

class HTTPRequest(BaseHTTPRequestHandler):
    def __init__(self, request_text):
        self.rfile = StringIO(request_text)
        self.raw_requestline = self.rfile.readline()
        self.error_code = self.error_message = None
        self.parse_request()

    def send_error(self, code, message):
        self.error_code = code
        self.error_message = message

class EchoProtocol(protocol.Protocol):
    def connectionMade(self):
        p=self.transport.getPeer()
        self.peer ='%s:%s' % (p.host,p.port)
        print "Connected from", self.peer
    def dataReceived(self,data):
        print data
        self.transport.write('You Sent:\n')
        self.transport.write(data)
        self.transport.loseConnection()
    def connectionLost(self,reason):
        print "Disconnected from %s: %s" % (self.peer,reason.value)

class CommandProtocol(protocol.Protocol):
    def connectionMade(self):
        p=self.transport.getPeer()
        self.peer ='%s:%s' % (p.host,p.port)
        print "Connected from", self.peer
    def dataReceived(self,data):
        print data
#        datamsg=mimetools.Message(StringIO.StringIO(data))
        dataHTTP=HTTPRequest(data)
        boundary=dataHTTP.headers['content-type'].split('boundary=')[-1]
        # strip multipart data
        kv=[datas.split('name="')[-1].split('"\n\r\n\r') for datas in data.split('--'+boundary+'--')]
        params={k:v.rstrip() for k,v in kv[:-1]}
        pdb.set_trace()
        self.transport.loseConnection()
    def connectionLost(self,reason):
        print "Disconnected from %s: %s" % (self.peer,reason.value)

        
factory=protocol.Factory()
factory.protocol=CommandProtocol

reactor.listenTCP(8082, factory)
def hello(): print 'Listening on port', 8082
reactor.callWhenRunning(hello)
reactor.run()