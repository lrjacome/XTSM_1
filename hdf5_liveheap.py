# -*- coding: utf-8 -*-
"""
Created on Tue May 21 15:19:04 2013

@author: Nate

This module defines a 'live heap' data class, which is essentially a stack
of like data items held in RAM, which are synchronously written out to an 
element in an hdf5 data file when the stack reaches a certain size.  
It is meant to provide transparent access to a heap of similar data taken
while some set of parameters is varied.  An example might be a series of 
images, each of which was taken at the end of a timing sequences generating
a BEC in an apparatus; each image corresponds to a specific iteration of the
experiment, labeled by a shotnumber.  As this data is accumulated, it can
be added to the live_heap, which manages the storage to disk by itself, and
retains the latest portion in RAM ready for analysis in a transparent way.
Ideally, the user does not need to be aware where the data currently resides.     
"""

class glab_liveheap():
    """
    This is the root class for live heaps.
    """
    def heave(self,heap_index):
        """
        this method should write the least precious (or directly specified) element 
        of the stack to hard disk, and remove the element from the RAMish data storage. 
        """
        pass
    def create_file(self):
        """
        this method will create a new hdf5 file to associate the data heap with
        """
        pass
    def add(self,element,shotnumber):
        """
        this will add an element to the heap; if necessary an existing element
        will be heaved from RAM to disk/file
        """
        pass
    def getlivedata(self):
        """
        this method returns all data currently in RAM
        """
        pass
    def getdata(self,shotnumbers):
        """
        this method will return data corresponding to a list of shotnumbers
        """
        pass
    def getshotnumbers(self,FILE_PERSIST):
        """
        this method will return all shot numbers present in the file (default), 
        or all live (RAMish) shotnumbers, as specified in the FILE_PERSIST flag
        """
        pass
    def getliveshotnumbers(self):
        """
        this will be superceded by a flag on getshotnumbers
        """
        pass
    def open_file(self):
        """
        this method opens the associated hdf5 data file on disk
        """
        pass
    def close_file(self):
        """
        this method closes the associated hdf5 data file on disk
        """
        pass
    def verify_file(self,ERROR_TYPE):
        """
        this function verifies the existence in the linked file of a variable to represent elements of this heap
        returns 1 on success, 0 on failure
        """
        pass
    def __init__(self,sizeof, element_structure, typecode, FILENAME, GENERATOR, DATANAME):
        """
        Constructor for live heaps
        """
        pass   