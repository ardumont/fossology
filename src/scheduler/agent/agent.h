/* **************************************************************
Copyright (C) 2010 Hewlett-Packard Development Company, L.P.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
************************************************************** */

#ifndef AGENT_H_INCLUDE
#define AGENT_H_INCLUDE

/* local includes */
#include <host.h>
#include <job.h>

/* unix library includes */
#include <sys/types.h>

/* glib includes */
#include <gio/gio.h>

/* ************************************************************************** */
/* **** Data Types ********************************************************** */
/* ************************************************************************** */

#define MAX_CMD  1023   ///< the maximum length for an agent's start command  (arbitrary)
#define MAX_NAME 255    ///< the maximum length for an agent's name           (arbitrary)
#define CHECK_TIME 120  ///< wait time between agent updates                  (arbitrary)

#define SAG_NONE 1        ///< There is nothing special about this agent
#define SAG_EXCLUSIVE 2   ///< This agent must not run at the same time as any other agent

/** Enum to keep track of the state of an agent */
typedef enum
{
  AG_FAILED = 0,  ///< AG_FAILED   The agent has failed during execution
  AG_CREATED = 1, ///< AG_CREATED  The agent has been allocated but is not running yet
  AG_SPAWNED = 2, ///< AG_SPAWNED  The agent has finished allocation but has registered work yet
  AG_RUNNING = 3, ///< AG_RUNNING  The agent has received a set of files to work on and is running
  AG_PAUSED = 4,  ///< AG_PAUSED   The agent is waiting either for new data or for processor time
  AG_CLOSED = 5   ///< AG_CLOSED   The agent has shut down, is no longer part of the system and should be destroyed
} agent_status;
extern const char* agent_status_strings[];

/**
 * Class to hold all of the information associated with creating a specific
 * type of agent.
 *
 * To create:
 *   meta_agent ma;
 *   meta_agent_init(&ma);
 *
 */
typedef struct meta_agent_internal* meta_agent;

/**
 * Class to hold all of the information associated with an agent.
 *
 * To create:
 *   agent a;
 *   agent_init(&a);
 */
typedef struct agent_internal* agent;

/**
 * TODO
 */
typedef int agent_pk;

/* ************************************************************************** */
/* **** Constructor Destructor ********************************************** */
/* ************************************************************************** */

/* meta agent */
void agent_list_clean();
void agent_list_init(void);
meta_agent meta_agent_init(char* name, char* cmd, int max, int spc);
void meta_agent_destroy(meta_agent ma);

/* agent */
agent agent_init(host host_machine, job owner);
void  agent_destroy(agent a);

/* ************************************************************************** */
/* **** Modifier Functions and events *************************************** */
/* ************************************************************************** */

void agent_death_event(pid_t* pids);
void agent_create_event(agent a);
void agent_ready_event(agent a);
void agent_update_event(void* unused);

void agent_transition(agent a, agent_status new_status);
void agent_pause(agent a);
void agent_unpause(agent a);
void agent_print_status(agent a, GOutputStream* ostr);
int  aprintf(agent a, const char* fmt, ...);
int  agent_pid(agent a);
ssize_t agent_write(agent a, const void* buf, int count);

agent_status agent_gstatus(agent a);

/* ************************************************************************** */
/* **** static functions and meta agents ************************************ */
/* ************************************************************************** */

void test_agents(host h);
void kill_agents(void);
void list_agents(GOutputStream* ostr);
int  add_meta_agent(char* name, char* cmd, int max, int spc);
int  is_meta_agent(char* name);
int  is_exclusive(char* name);
int  num_agents(void);

#endif /* AGENT_H_INCLUDE */