////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// STL A* Search implementation
// (C)2001 Justin Heyes-Jones
// Licence : MIT
//
// Finding a path on a simple grid maze
// This shows how to do shortest path finding using A*

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

#include "stlastar.h" // See header for copyright and usage information

#include <iostream>
#include <stdio.h>
#include <sstream>
#include <cstdlib>

#define DEBUG_LISTS 0
#define DEBUG_LIST_LENGTHS_ONLY 0

using namespace std;

// Global data

// The world map, we initialize the work map with a maximum map size

const int IMPASSABLE_COST = 999;
const int MAX_MAP_WIDTH = 200;
const int MAX_MAP_HEIGHT = 200;
int MAP_WIDTH = MAX_MAP_WIDTH;
int MAP_HEIGHT = MAX_MAP_HEIGHT;
int world_map[MAX_MAP_WIDTH*MAX_MAP_HEIGHT];

// map helper functions

int GetMap( int x, int y )
{
	if( x < 0 ||
	    x >= MAP_WIDTH ||
		 y < 0 ||
		 y >= MAP_HEIGHT
	  )
	{
		return IMPASSABLE_COST;	 
	}

	return world_map[(y*MAP_WIDTH)+x];
}



// Definitions

class MapSearchNode
{
public:
	int x;	 // the (x,y) positions of the node
	int y;	
	
	MapSearchNode() { x = y = 0; }
	MapSearchNode( int px, int py ) { x=px; y=py; }

	float GoalDistanceEstimate( MapSearchNode &nodeGoal );
	bool IsGoal( MapSearchNode &nodeGoal );
	bool GetSuccessors( AStarSearch<MapSearchNode> *astarsearch, MapSearchNode *parent_node );
	float GetCost( MapSearchNode &successor );
	bool IsSameState( MapSearchNode &rhs );

	void PrintNodeInfo(); 


};

bool MapSearchNode::IsSameState( MapSearchNode &rhs )
{

	// same state in a maze search is simply when (x,y) are the same
	if( (x == rhs.x) &&
		(y == rhs.y) )
	{
		return true;
	}
	else
	{
		return false;
	}

}

void MapSearchNode::PrintNodeInfo()
{
	char str[100];
	sprintf( str, "{'x':%d,'y':%d,'cost':%d}\n", x,y,GetMap(x,y) );

	cout << str;
}

// Here's the heuristic function that estimates the distance from a Node
// to the Goal. 

float MapSearchNode::GoalDistanceEstimate( MapSearchNode &nodeGoal )
{
	float xd = float( ( (float)x - (float)nodeGoal.x ) );
	float yd = float( ( (float)y - (float)nodeGoal.y) );

	return xd + yd;

}

bool MapSearchNode::IsGoal( MapSearchNode &nodeGoal )
{

	if( (x == nodeGoal.x) &&
		(y == nodeGoal.y) )
	{
		return true;
	}

	return false;
}

// This generates the successors to the given Node. It uses a helper function called
// AddSuccessor to give the successors to the AStar class. The A* specific initialisation
// is done for each node internally, so here you just set the state information that
// is specific to the application
bool MapSearchNode::GetSuccessors( AStarSearch<MapSearchNode> *astarsearch, MapSearchNode *parent_node )
{

	int parent_x = -1; 
	int parent_y = -1; 

	if( parent_node )
	{
		parent_x = parent_node->x;
		parent_y = parent_node->y;
	}
	

	MapSearchNode NewNode;

	// push each possible move except allowing the search to go backwards

	if( (GetMap( x-1, y ) < IMPASSABLE_COST) 
		&& !((parent_x == x-1) && (parent_y == y))
	  ) 
	{
		NewNode = MapSearchNode( x-1, y );
		astarsearch->AddSuccessor( NewNode );
	}	

	if( (GetMap( x, y-1 ) < IMPASSABLE_COST) 
		&& !((parent_x == x) && (parent_y == y-1))
	  ) 
	{
		NewNode = MapSearchNode( x, y-1 );
		astarsearch->AddSuccessor( NewNode );
	}	

	if( (GetMap( x+1, y ) < IMPASSABLE_COST)
		&& !((parent_x == x+1) && (parent_y == y))
	  ) 
	{
		NewNode = MapSearchNode( x+1, y );
		astarsearch->AddSuccessor( NewNode );
	}	

		
	if( (GetMap( x, y+1 ) < IMPASSABLE_COST) 
		&& !((parent_x == x) && (parent_y == y+1))
		)
	{
		NewNode = MapSearchNode( x, y+1 );
		astarsearch->AddSuccessor( NewNode );
	}	

	return true;
}

// given this node, what does it cost to move to successor. In the case
// of our map the answer is the map terrain value at this node since that is 
// conceptually where we're moving

float MapSearchNode::GetCost( MapSearchNode &successor )
{
	return (float) GetMap( x, y );

}

// Main use : width height costs startX startY endX endY
int main( int argc, char *argv[] )
{
    if (!(istringstream(argv[1]) >> MAP_WIDTH) || MAP_WIDTH > MAX_MAP_WIDTH) {
        cerr << "Invalid mapWidth " << argv[1] << '\n';
        return -1;
    }

    if (!(istringstream(argv[2]) >> MAP_HEIGHT) || MAP_HEIGHT > MAX_MAP_HEIGHT) {
        cerr << "Invalid mapHeight " << argv[2] << '\n';
        return -1;
    }

    std::string costs = argv[3];

    std::string buffer = "";
    int number;
    int index = 0;
    for(std::string::iterator it = costs.begin(); it <= costs.end(); ++it) {
        if (*it == ',' || costs.end() == it) {
            istringstream(buffer) >> number;
            world_map[index] = number;
            buffer = "";
            index++;
        } else {
            buffer = buffer + *it;
        }
    }

    int startX;
    if (!(istringstream(argv[4]) >> startX) || startX > MAP_WIDTH - 1) {
        cerr << "Invalid startX " << argv[4] << '\n';
        return -1;
    }
    int startY;
    if (!(istringstream(argv[5]) >> startY) || startY > MAP_HEIGHT - 1) {
        cerr << "Invalid startY " << argv[5] << '\n';
        return -1;
    }

    int endX;
    if (!(istringstream(argv[6]) >> endX) || endX > MAP_WIDTH - 1) {
        cerr << "Invalid endX " << argv[6] << '\n';
        return -1;
    }
    int endY;
    if (!(istringstream(argv[7]) >> endY) || endY > MAP_HEIGHT - 1) {
        cerr << "Invalid endY " << argv[7] << '\n';
        return -1;
    }

	// Our sample problem defines the world as a 2d array representing a terrain
	// Each element contains an integer from 0 to IMPASSABLE_COST-1 which indicates the cost 
	// of travel across the terrain. Zero means the least possible difficulty 
	// in travelling (think ice rink if you can skate) whilst IMPASSABLE_COST-1 represents the 
	// most difficult. IMPASSABLE_COST indicates that we cannot pass.

	// Create an instance of the search class...

	AStarSearch<MapSearchNode> astarsearch;

	unsigned int SearchCount = 0;

	const unsigned int NumSearches = 1;

	while(SearchCount < NumSearches)
	{

		// Create a start state
		MapSearchNode nodeStart;
		nodeStart.x = startX;
		nodeStart.y = startY;

		// Define the goal state
		MapSearchNode nodeEnd;
		nodeEnd.x = endX;
		nodeEnd.y = endY;
		
		// Set Start and goal states
		
		astarsearch.SetStartAndGoalStates( nodeStart, nodeEnd );

		unsigned int SearchState;
		unsigned int SearchSteps = 0;

		do
		{
			SearchState = astarsearch.SearchStep();

			SearchSteps++;

	#if DEBUG_LISTS

			cout << "Steps:" << SearchSteps << "\n";

			int len = 0;

			cout << "Open:\n";
			MapSearchNode *p = astarsearch.GetOpenListStart();
			while( p )
			{
				len++;
	#if !DEBUG_LIST_LENGTHS_ONLY			
				((MapSearchNode *)p)->PrintNodeInfo();
	#endif
				p = astarsearch.GetOpenListNext();
				
			}

			cout << "Open list has " << len << " nodes\n";

			len = 0;

			cout << "Closed:\n";
			p = astarsearch.GetClosedListStart();
			while( p )
			{
				len++;
	#if !DEBUG_LIST_LENGTHS_ONLY			
				p->PrintNodeInfo();
	#endif			
				p = astarsearch.GetClosedListNext();
			}

			cout << "Closed list has " << len << " nodes\n";
	#endif

		}
		while( SearchState == AStarSearch<MapSearchNode>::SEARCH_STATE_SEARCHING );

		if( SearchState == AStarSearch<MapSearchNode>::SEARCH_STATE_SUCCEEDED )
		{
				MapSearchNode *node = astarsearch.GetSolutionStart();

				int steps = 0;

				node->PrintNodeInfo();
				for( ;; )
				{
					node = astarsearch.GetSolutionNext();

					if( !node )
					{
						break;
					}

					node->PrintNodeInfo();
					steps ++;
				
				};

				// cout << "Solution steps " << steps << endl;

				// Once you're done with the solution you can free the nodes up
				astarsearch.FreeSolutionNodes();

	
		}
		else if( SearchState == AStarSearch<MapSearchNode>::SEARCH_STATE_FAILED ) 
		{
			cout << "Search terminated. Did not find goal state\n";

            return -1;
		}

		// Display the number of loops the search went through
		// cout << "SearchSteps : " << SearchSteps << "\n";

		SearchCount ++;

		astarsearch.EnsureMemoryFreed();
	}
	
	return 0;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
